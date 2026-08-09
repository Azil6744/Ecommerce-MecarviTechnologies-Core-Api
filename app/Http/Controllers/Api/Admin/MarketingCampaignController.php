<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarketingCampaignController extends Controller
{
    private array $channels = [
        MarketingCampaign::CHANNEL_EMAIL,
        MarketingCampaign::CHANNEL_SMS,
        MarketingCampaign::CHANNEL_PUSH,
    ];

    public function compose(Request $request, string $channel)
    {
        if (! in_array($channel, $this->channels, true)) {
            return response()->json(['success' => false, 'message' => 'Unsupported campaign channel.'], 404);
        }

        $campaign = MarketingCampaign::query()
            ->where('channel', $channel)
            ->where('status', 'draft')
            ->latest('updated_at')
            ->first();

        $payload = $campaign ? $campaign->toAdminArray() : $this->defaults($channel);

        $totalContacts = User::query();
        if ($channel === MarketingCampaign::CHANNEL_SMS) {
            $totalContacts->whereNotNull('phone')->where('phone', '!=', '');
        } else {
            $totalContacts->whereNotNull('email')->where('email', '!=', '');
        }
        $totalContactsCount = $totalContacts->count();

        return response()->json([
            'success' => true,
            'data' => [
                'campaign' => $payload,
                'summary' => $this->summary($payload),
                'segments' => $this->segments($channel),
                'total_contacts' => $totalContactsCount,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'channel' => ['nullable', Rule::in($this->channels)],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        $query = MarketingCampaign::query()->latest();

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['scheduled', 'sent', 'test_sent']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'campaigns' => $query->paginate((int) $request->get('per_page', 15)),
            ],
        ]);
    }

    public function show(MarketingCampaign $marketingCampaign)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'campaign' => $marketingCampaign->toAdminArray(),
                'summary' => $this->summary($marketingCampaign->toAdminArray()),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedCampaign($request);
        $campaign = $this->saveCampaign($request, $validated, $request->input('status', 'draft'));

        return response()->json([
            'success' => true,
            'message' => 'Campaign draft saved.',
            'data' => [
                'campaign' => $campaign->toAdminArray(),
                'summary' => $this->summary($campaign->toAdminArray()),
            ],
        ], $campaign->wasRecentlyCreated ? 201 : 200);
    }

    public function send(Request $request)
    {
        $validated = $this->validatedCampaign($request);
        $scheduleType = $request->input('schedule_type', $validated['schedule_type'] ?? 'now');
        $status = $scheduleType === 'later' ? 'scheduled' : 'sent';
        $campaign = $this->saveCampaign($request, $validated, $status);

        $metrics = [
            'total_target' => 0,
            'sent' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        if ($status === 'sent') {
            $channel = $campaign->channel;
            $recipients = [];

            if ($campaign->audience_type === 'custom') {
                $recipients = array_values(array_filter((array) ($campaign->custom_recipients ?? [])));
            } else {
                $query = User::query();
                if ($campaign->audience_type === 'segment') {
                    $query = $this->getSegmentQuery($campaign->segment ?? 'Active Customers', $channel);
                } else {
                    if ($channel === MarketingCampaign::CHANNEL_SMS) {
                        $query->whereNotNull('phone')->where('phone', '!=', '');
                    } else {
                        $query->whereNotNull('email')->where('email', '!=', '');
                    }
                }

                if ($channel === MarketingCampaign::CHANNEL_SMS) {
                    $recipients = $query->pluck('phone')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                } else {
                    $recipients = $query->pluck('email')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }
            }

            $metrics['total_target'] = count($recipients);

            if ($channel === MarketingCampaign::CHANNEL_SMS) {
                $smsService = app(\App\Services\SmsService::class);
                $body = $campaign->body ?: 'Special update from Mecarvi Embroidery';

                foreach ($recipients as $phone) {
                    $err = null;
                    $ok = $smsService->sendSms((string) $phone, $body, $err);
                    if ($ok) {
                        $metrics['sent']++;
                    } else {
                        $metrics['failed']++;
                        if ($err && count($metrics['errors']) < 5) {
                            $metrics['errors'][] = "Phone {$phone}: {$err}";
                        }
                    }
                }
            } elseif ($channel === MarketingCampaign::CHANNEL_EMAIL) {
                $emailService = app(\App\Services\EmailNotificationService::class);
                $subject = $campaign->subject ?: 'Special Offer from Mecarvi Embroidery';
                $body = $campaign->body ?: 'Special Offer';

                foreach ($recipients as $email) {
                    $overrideData = [
                        'event_key' => 'marketing_campaign',
                        'data' => [
                            'customer_name' => 'Valued Customer',
                            'customer_email' => (string) $email,
                            'subject' => $subject,
                            'heading' => $subject,
                            'body_text' => $body,
                            'site_name' => config('app.name', 'Mecarvi Embroidery'),
                        ],
                    ];
                    $log = $emailService->sendTest((string) $email, $overrideData);
                    if ($log->status === 'sent') {
                        $metrics['sent']++;
                    } else {
                        $metrics['failed']++;
                        if ($log->error_message && count($metrics['errors']) < 5) {
                            $metrics['errors'][] = "Email {$email}: {$log->error_message}";
                        }
                    }
                }
            } else {
                $metrics['sent'] = count($recipients);
            }

            $campaign->forceFill([
                'sent_at' => now(),
                'metrics' => $metrics,
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => $status === 'scheduled'
                ? 'Campaign scheduled.'
                : "Campaign bulk send completed. Sent: {$metrics['sent']}, Failed: {$metrics['failed']}.",
            'data' => [
                'campaign' => $campaign->fresh()->toAdminArray(),
                'summary' => $this->summary($campaign->fresh()->toAdminArray()),
                'metrics' => $metrics,
            ],
        ]);
    }

    public function test(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'nullable|integer|exists:marketing_campaigns,id',
            'channel' => ['required', Rule::in($this->channels)],
            'recipient' => 'required|string|max:255',
            'payload' => 'nullable|array',
        ]);

        $campaign = isset($validated['campaign_id'])
            ? MarketingCampaign::find($validated['campaign_id'])
            : null;

        $channel = $validated['channel'];
        $recipient = trim($validated['recipient']);
        $payload = $validated['payload'] ?? [];
        $messageBody = $payload['body'] ?? $campaign?->body ?? $payload['notification_message'] ?? $campaign?->notification_message ?? 'Test notification from Mecarvi Embroidery';
        $subject = $payload['subject'] ?? $campaign?->subject ?? $payload['notification_title'] ?? $campaign?->notification_title ?? 'Test Campaign';

        $delivered = false;
        $errorMessage = null;

        if ($channel === MarketingCampaign::CHANNEL_SMS) {
            $smsService = app(\App\Services\SmsService::class);
            $delivered = $smsService->sendSms($recipient, $messageBody, $errorMessage);
        } elseif ($channel === MarketingCampaign::CHANNEL_EMAIL) {
            $emailService = app(\App\Services\EmailNotificationService::class);
            $overrideData = [
                'event_key' => 'marketing_test',
                'data' => [
                    'customer_name' => 'Test Recipient',
                    'customer_email' => $recipient,
                    'subject' => $subject,
                    'heading' => $subject,
                    'body_text' => $messageBody,
                    'site_name' => config('app.name', 'Mecarvi Embroidery'),
                ],
            ];
            $log = $emailService->sendTest($recipient, $overrideData);
            $delivered = ($log->status === 'sent');
            if (! $delivered) {
                $errorMessage = $log->error_message ?? 'Email delivery failed.';
            }
        } elseif ($channel === MarketingCampaign::CHANNEL_PUSH) {
            $delivered = true;
        }

        if ($campaign) {
            $campaign->forceFill([
                'last_test' => [
                    'recipient' => $recipient,
                    'channel' => $channel,
                    'delivered' => $delivered,
                    'error' => $errorMessage,
                    'payload' => $payload,
                    'tested_at' => now()->toIso8601String(),
                ],
            ])->save();
        }

        return response()->json([
            'success' => $delivered,
            'message' => $delivered
                ? 'Test campaign sent successfully.'
                : ('Test failed: ' . ($errorMessage ?? 'Failed to deliver test message.')),
            'data' => [
                'delivered' => $delivered,
                'provider_status' => $delivered ? 'delivered' : 'failed',
                'error' => $errorMessage,
                'campaign' => $campaign?->fresh()?->toAdminArray(),
            ],
        ], $delivered ? 200 : 422);
    }

    private function saveCampaign(Request $request, array $validated, string $status): MarketingCampaign
    {
        $payload = array_merge($this->defaults($validated['channel']), $validated);
        $payload['recipients_count'] = $this->recipientCount($payload);
        $payload['status'] = $status;
        $payload['created_by'] = $request->user()?->id;

        if (! empty($payload['scheduled_at'])) {
            $payload['scheduled_at'] = Carbon::parse($payload['scheduled_at']);
        }

        if ($status === 'draft' && empty($payload['id'])) {
            $campaign = MarketingCampaign::query()
                ->where('channel', $payload['channel'])
                ->where('status', 'draft')
                ->latest('updated_at')
                ->first();
        } else {
            $campaign = ! empty($payload['id']) ? MarketingCampaign::find($payload['id']) : null;
        }

        if ($campaign) {
            $campaign->fill($payload)->save();
            return $campaign->fresh();
        }

        return MarketingCampaign::create($payload);
    }

    private function validatedCampaign(Request $request): array
    {
        try {
            return $request->validate([
                'id' => 'nullable|integer|exists:marketing_campaigns,id',
                'channel' => ['required', Rule::in($this->channels)],
                'name' => 'required|string|max:255',
                'audience_type' => 'nullable|string|max:30',
                'segment' => 'nullable|string|max:255',
                'custom_recipients' => 'nullable|array',
                'from_name' => 'nullable|string|max:255',
                'from_email' => 'nullable|email|max:255',
                'reply_to' => 'nullable|string|max:255',
                'subject' => 'nullable|string|max:255',
                'preview_text' => 'nullable|string|max:500',
                'content_type' => 'nullable|string|max:255',
                'body' => 'nullable|string',
                'sender_name' => 'nullable|string|max:255',
                'sender_phone' => 'nullable|string|max:255',
                'notification_title' => 'nullable|string|max:255',
                'notification_message' => 'nullable|string',
                'deep_link' => 'nullable|string|max:1000',
                'platforms' => 'nullable|array',
                'image_path' => 'nullable|string|max:1000',
                'schedule_type' => 'nullable|string|max:30',
                'scheduled_at' => 'nullable|date',
                'timezone' => 'nullable|string|max:255',
                'settings' => 'nullable|array',
                'metrics' => 'nullable|array',
                'status' => 'nullable|string|max:30',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    private function summary(array $campaign): array
    {
        $count = $this->recipientCount($campaign);
        $channel = $campaign['channel'];
        $message = $channel === 'push'
            ? ($campaign['notification_message'] ?? '')
            : ($campaign['body'] ?? '');

        return [
            'recipients' => $count,
            'segment' => $campaign['segment'] ?? 'Active Customers',
            'from' => trim(($campaign['from_name'] ?? '') . ' ' . ($campaign['from_email'] ?? '')),
            'reply_to' => $campaign['reply_to'] ?? null,
            'subject' => $campaign['subject'] ?? ($campaign['notification_title'] ?? null),
            'preview_text' => $campaign['preview_text'] ?? null,
            'content_type' => $campaign['content_type'] ?? ($channel === 'email' ? 'HTML Email' : strtoupper($channel)),
            'sender' => $campaign['sender_name'] ?? $campaign['from_name'] ?? 'Mecarvi Embroidery',
            'message_preview' => $this->truncate($message ?: ($campaign['subject'] ?? $campaign['notification_title'] ?? ''), 45),
            'total_sms' => $channel === 'sms' ? max(1, (int) ceil(strlen($message) / 160)) : null,
            'platforms' => $campaign['platforms'] ?? [],
            'deep_link' => $campaign['deep_link'] ?? null,
            'estimated_delivery' => $this->estimatedDelivery($campaign),
        ];
    }

    private function recipientCount(array $campaign): int
    {
        $channel = $campaign['channel'] ?? 'email';
        if (($campaign['audience_type'] ?? 'segment') === 'custom') {
            return count($campaign['custom_recipients'] ?? []);
        }

        if (($campaign['audience_type'] ?? 'segment') === 'all') {
            $totalContacts = User::query();
            if ($channel === MarketingCampaign::CHANNEL_SMS) {
                $totalContacts->whereNotNull('phone')->where('phone', '!=', '');
            } else {
                $totalContacts->whereNotNull('email')->where('email', '!=', '');
            }
            return $totalContacts->count();
        }

        $segmentName = $campaign['segment'] ?? 'Active Customers';
        return $this->getSegmentQuery($segmentName, $channel)->count();
    }

    private function getSegmentQuery(string $segmentName, string $channel)
    {
        $query = User::query()->where('role', 'customer');

        if ($segmentName === 'Active Customers') {
            $query->whereNull('banned_at')
                  ->whereNull('deactivated_at');
        } elseif ($segmentName === 'VIP Customers') {
            $query->whereNull('banned_at')
                  ->whereNull('deactivated_at')
                  ->where(function ($q) {
                      $q->where('loyalty_points', '>', 0)
                        ->orWhere('wallet_balance', '>', 0);
                  });
        }

        if ($channel === MarketingCampaign::CHANNEL_SMS) {
            $query->whereNotNull('phone')->where('phone', '!=', '');
        } elseif ($channel === MarketingCampaign::CHANNEL_EMAIL) {
            $query->whereNotNull('email')->where('email', '!=', '');
        }

        return $query;
    }

    private function segments(string $channel = 'email'): array
    {
        $activeCount = $this->getSegmentQuery('Active Customers', $channel)->count();
        $allCount = $this->getSegmentQuery('All Customers', $channel)->count();
        $vipCount = $this->getSegmentQuery('VIP Customers', $channel)->count();

        return [
            ['label' => 'Active Customers', 'value' => 'Active Customers', 'count' => $activeCount],
            ['label' => 'All Customers', 'value' => 'All Customers', 'count' => $allCount],
            ['label' => 'VIP Customers', 'value' => 'VIP Customers', 'count' => $vipCount],
        ];
    }

    private function estimatedDelivery(array $campaign): ?string
    {
        if (($campaign['schedule_type'] ?? 'now') === 'now') {
            return 'Immediate';
        }

        if (! empty($campaign['scheduled_at'])) {
            return Carbon::parse($campaign['scheduled_at'])->format('M j, Y h:i A');
        }

        return 'Scheduled';
    }

    private function truncate(string $value, int $length): string
    {
        return strlen($value) > $length ? substr($value, 0, $length - 3) . '...' : $value;
    }

    private function defaults(string $channel): array
    {
        $base = [
            'id' => null,
            'channel' => $channel,
            'audience_type' => 'segment',
            'segment' => 'Active Customers',
            'custom_recipients' => [],
            'schedule_type' => 'now',
            'scheduled_at' => null,
            'timezone' => '(GMT-04:00) Eastern Time (US & Canada)',
            'status' => 'draft',
            'settings' => ['fallback_recipients' => 0],
            'metrics' => [],
        ];

        $base['recipients_count'] = $this->recipientCount($base);
        $base['settings']['fallback_recipients'] = $base['recipients_count'];

        if ($channel === MarketingCampaign::CHANNEL_SMS) {
            return array_merge($base, [
                'name' => 'Summer Sale SMS',
                'sender_name' => 'Mecarvi Embroidery',
                'reply_to' => '+1 (555) 123-4567',
                'body' => "Summer Sale is Here!\nGet up to 50% OFF on your favorite embroidery services.\nHurry, offer ends soon!\nShop now: bit.ly/mecarvi-sale\nReply STOP to opt out.",
                'settings' => array_merge($base['settings'], [
                    'include_unsubscribe' => true,
                    'track_clicks' => true,
                    'delivery_reports' => true,
                    'quiet_hours' => false,
                ]),
            ]);
        }

        if ($channel === MarketingCampaign::CHANNEL_PUSH) {
            return array_merge($base, [
                'name' => 'Summer Sale Push',
                'notification_title' => 'Summer Sale is Here!',
                'notification_message' => "Get up to 50% OFF on your favorite embroidery services.\nHurry, offer ends soon!",
                'deep_link' => 'mecarviembroidery.com/sale',
                'platforms' => ['android', 'ios', 'web'],
                'settings' => array_merge($base['settings'], [
                    'send_active_only' => true,
                    'do_not_disturb' => false,
                    'track_clicks' => true,
                    'sound' => true,
                    'badge_count' => true,
                    'expiration_ttl' => false,
                ]),
            ]);
        }

        return array_merge($base, [
            'name' => 'Summer Sale - Exclusive Offers',
            'from_name' => 'Mecarvi Embroidery',
            'from_email' => 'marketing@mecarviembroidery.com',
            'reply_to' => 'marketing@mecarviembroidery.com',
            'subject' => 'Summer Sale is Here! Get Up to 50% Off',
            'preview_text' => 'Limited time offers on your favorite embroidery services. Shop now!',
            'content_type' => 'HTML Email',
            'body' => 'Special Offer Just For You - SUMMER SALE - UP TO 50% OFF',
        ]);
    }
}
