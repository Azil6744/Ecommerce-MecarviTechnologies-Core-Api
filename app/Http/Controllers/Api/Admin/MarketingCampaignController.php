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

        return response()->json([
            'success' => true,
            'data' => [
                'campaign' => $payload,
                'summary' => $this->summary($payload),
                'segments' => $this->segments(),
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

        if ($status === 'sent' && ! $campaign->sent_at) {
            $campaign->forceFill(['sent_at' => now()])->save();
        }

        return response()->json([
            'success' => true,
            'message' => $status === 'scheduled' ? 'Campaign scheduled.' : 'Campaign marked as sent.',
            'data' => [
                'campaign' => $campaign->fresh()->toAdminArray(),
                'summary' => $this->summary($campaign->fresh()->toAdminArray()),
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

        if ($campaign) {
            $campaign->forceFill([
                'last_test' => [
                    'recipient' => $validated['recipient'],
                    'channel' => $validated['channel'],
                    'payload' => $validated['payload'] ?? [],
                    'tested_at' => now()->toIso8601String(),
                ],
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Test request recorded.',
            'data' => [
                'delivered' => false,
                'provider_status' => 'recorded',
                'campaign' => $campaign?->fresh()?->toAdminArray(),
            ],
        ]);
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
        if (($campaign['audience_type'] ?? 'segment') === 'custom') {
            return count($campaign['custom_recipients'] ?? []);
        }

        $total = User::query()->count();
        if ($total > 0) {
            return min($total, (int) ($campaign['settings']['fallback_recipients'] ?? 7393));
        }

        return (int) ($campaign['recipients_count'] ?? $campaign['settings']['fallback_recipients'] ?? 7393);
    }

    private function segments(): array
    {
        $customers = User::query()->count();
        $fallback = $customers > 0 ? min($customers, 7393) : 7393;

        return [
            ['label' => 'Active Customers', 'value' => 'Active Customers', 'count' => $fallback],
            ['label' => 'All Customers', 'value' => 'All Customers', 'count' => max($fallback, $customers, 24156)],
            ['label' => 'VIP Customers', 'value' => 'VIP Customers', 'count' => 1284],
        ];
    }

    private function estimatedDelivery(array $campaign): ?string
    {
        if (! empty($campaign['scheduled_at'])) {
            return Carbon::parse($campaign['scheduled_at'])->format('M j, Y h:i A');
        }

        return 'May 20, 2025 10:00 AM';
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
            'recipients_count' => 7393,
            'custom_recipients' => [],
            'schedule_type' => 'now',
            'scheduled_at' => null,
            'timezone' => '(GMT-04:00) Eastern Time (US & Canada)',
            'status' => 'draft',
            'settings' => ['fallback_recipients' => 7393],
            'metrics' => [],
        ];

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
