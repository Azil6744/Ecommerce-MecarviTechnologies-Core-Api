<?php

namespace App\Services;

use App\Models\EcommerceOrder;
use App\Models\EmailNotificationLog;
use App\Models\EmailNotificationSetting;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailNotificationService
{
    public const EVENTS = [
        'user_registered' => [
            'label' => 'User Registered',
            'category' => 'onboarding',
            'subject' => 'Welcome to Mecarvi Embroidery',
            'heading' => 'Welcome, {{customer_name}}',
            'body_text' => "Thank you for registering with Mecarvi Embroidery.\n\nYou can now track orders, save details, and manage your account.",
            'variables' => ['customer_name', 'customer_email', 'site_name'],
        ],
        'order_placed' => [
            'label' => 'Order Placed',
            'category' => 'orders',
            'subject' => 'Your order {{order_number}} has been received',
            'heading' => 'Order received',
            'body_text' => "Hi {{customer_name}},\n\nThank you for your order. We received order {{order_number}} and our team will review it shortly.\n\nOrder total: {{order_total}}",
            'variables' => ['customer_name', 'customer_email', 'order_number', 'order_total', 'order_status', 'site_name'],
        ],
        'order_status_changed' => [
            'label' => 'Order Status Changed',
            'category' => 'orders',
            'subject' => 'Order {{order_number}} status updated',
            'heading' => 'Order status updated',
            'body_text' => "Hi {{customer_name}},\n\nYour order {{order_number}} is now {{order_status}}.",
            'variables' => ['customer_name', 'customer_email', 'order_number', 'order_total', 'order_status', 'tracking_number', 'tracking_url'],
        ],
        'order_shipped' => [
            'label' => 'Order Shipped',
            'category' => 'orders',
            'subject' => 'Order {{order_number}} has shipped',
            'heading' => 'Your order is on the way',
            'body_text' => "Hi {{customer_name}},\n\nYour order {{order_number}} has shipped.\n\nTracking number: {{tracking_number}}",
            'button_text' => 'Track order',
            'button_url' => '{{tracking_url}}',
            'variables' => ['customer_name', 'customer_email', 'order_number', 'order_total', 'tracking_number', 'tracking_url'],
        ],
        'order_delivered' => [
            'label' => 'Order Delivered',
            'category' => 'orders',
            'subject' => 'Order {{order_number}} has been delivered',
            'heading' => 'Your order was delivered',
            'body_text' => "Hi {{customer_name}},\n\nYour order {{order_number}} has been marked as delivered. Thank you for choosing Mecarvi Embroidery.",
            'variables' => ['customer_name', 'customer_email', 'order_number', 'order_total'],
        ],
        'order_cancelled' => [
            'label' => 'Order Cancelled',
            'category' => 'orders',
            'subject' => 'Order {{order_number}} was cancelled',
            'heading' => 'Order cancelled',
            'body_text' => "Hi {{customer_name}},\n\nYour order {{order_number}} has been cancelled. If you have questions, please contact our support team.",
            'variables' => ['customer_name', 'customer_email', 'order_number', 'order_total'],
        ],
        'quote_submitted' => [
            'label' => 'Quote Submitted',
            'category' => 'sales',
            'subject' => 'We received your quote request {{quote_number}}',
            'heading' => 'Quote request received',
            'body_text' => "Hi {{customer_name}},\n\nWe received your quote request {{quote_number}}. Our team will review the details and follow up soon.",
            'variables' => ['customer_name', 'customer_email', 'quote_number', 'quote_total'],
        ],
        'gift_card_issued' => [
            'label' => 'Gift Card Issued',
            'category' => 'sales',
            'subject' => 'Your gift card is ready',
            'heading' => 'Gift card ready',
            'body_text' => "Hi {{customer_name}},\n\nYour gift card is ready.\n\nCode: {{gift_card_code}}\nBalance: {{gift_card_balance}}",
            'variables' => ['customer_name', 'customer_email', 'gift_card_code', 'gift_card_balance'],
        ],
    ];

    public function ensureDefaultTemplates(): void
    {
        foreach (self::EVENTS as $eventKey => $definition) {
            EmailTemplate::firstOrCreate(
                ['event_key' => $eventKey],
                [
                    'name' => $definition['label'],
                    'slug' => $eventKey,
                    'subject' => $definition['subject'],
                    'category' => $definition['category'],
                    'heading' => $definition['heading'],
                    'body_text' => $definition['body_text'],
                    'button_text' => $definition['button_text'] ?? null,
                    'button_url' => $definition['button_url'] ?? null,
                    'footer_text' => 'Mecarvi Embroidery',
                    'status' => 'published',
                    'variables' => $definition['variables'],
                    'send_to_customer' => true,
                    'send_to_admin' => false,
                    'admin_recipients' => [],
                ]
            );
        }
    }

    public function setting(): EmailNotificationSetting
    {
        return EmailNotificationSetting::firstOrCreate([], [
            'is_enabled' => false,
            'mailer' => 'smtp',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'from_name' => config('mail.from.name', config('app.name')),
            'from_email' => config('mail.from.address'),
        ]);
    }

    public function sendEvent(string $eventKey, array $data, ?string $customerEmail = null): array
    {
        $this->ensureDefaultTemplates();

        $setting = $this->setting();
        $template = EmailTemplate::where('event_key', $eventKey)->first();
        $results = [];

        if (! $setting->is_enabled) {
            return [$this->logSkipped($eventKey, $template, $customerEmail ?: 'unknown', 'Email sending is disabled.', $data)];
        }

        if (! $template || $template->status !== 'published') {
            return [$this->logSkipped($eventKey, $template, $customerEmail ?: 'unknown', 'No active template for this event.', $data)];
        }

        if ($template->send_to_customer && $customerEmail) {
            $results[] = $this->sendTo($eventKey, $template, $customerEmail, 'customer', $data);
        }

        if ($template->send_to_admin) {
            foreach ($this->adminRecipients($template, $setting) as $adminEmail) {
                $results[] = $this->sendTo($eventKey, $template, $adminEmail, 'admin', $data);
            }
        }

        return $results;
    }

    public function sendTest(string $recipientEmail, array $override = []): EmailNotificationLog
    {
        $this->ensureDefaultTemplates();

        $template = EmailTemplate::where('event_key', $override['event_key'] ?? 'order_placed')->first();
        $data = array_merge([
            'customer_name' => 'Test Customer',
            'customer_email' => $recipientEmail,
            'order_number' => 'ORD-TEST-001',
            'order_total' => '$125.00',
            'order_status' => 'pending',
            'site_name' => config('app.name', 'Mecarvi Embroidery'),
            'tracking_number' => 'TEST123456',
            'tracking_url' => url('/'),
        ], $override['data'] ?? []);

        return $this->sendTo('test_email', $template, $recipientEmail, 'test', $data);
    }

    public function sendOrderEvent(string $eventKey, EcommerceOrder $order): array
    {
        $order->loadMissing('items');

        return $this->sendEvent($eventKey, $this->orderData($order), $order->customer_email);
    }

    public function orderData(EcommerceOrder $order): array
    {
        return [
            'customer_name' => $order->customer_name ?: 'Customer',
            'customer_email' => $order->customer_email,
            'order_number' => $order->order_number,
            'order_total' => '$' . number_format((float) $order->total_amount, 2),
            'order_status' => Str::headline((string) $order->status),
            'tracking_number' => $order->tracking_number ?: '',
            'tracking_url' => $order->tracking_url ?: '',
            'estimated_delivery' => optional($order->estimated_delivery_at)->format('M j, Y') ?: '',
            'site_name' => config('app.name', 'Mecarvi Embroidery'),
        ];
    }

    private function sendTo(string $eventKey, ?EmailTemplate $template, string $recipientEmail, string $recipientType, array $data): EmailNotificationLog
    {
        $subject = $this->replaceVariables($template?->subject ?: 'Mecarvi Embroidery', $data);
        $html = $this->renderHtml($template, $data);

        $log = EmailNotificationLog::create([
            'event_key' => $eventKey,
            'email_template_id' => $template?->id,
            'recipient_email' => $recipientEmail,
            'recipient_type' => $recipientType,
            'subject' => $subject,
            'status' => 'pending',
            'payload' => $data,
        ]);

        try {
            $this->applyMailConfig($this->setting());

            Mail::html($html, function ($message) use ($recipientEmail, $subject) {
                $message->to($recipientEmail)->subject($subject);
            });

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('Email notification failed: ' . $e->getMessage(), [
                'event_key' => $eventKey,
                'recipient' => $recipientEmail,
            ]);
        }

        return $log->fresh();
    }

    private function logSkipped(string $eventKey, ?EmailTemplate $template, string $recipientEmail, string $reason, array $data): EmailNotificationLog
    {
        return EmailNotificationLog::create([
            'event_key' => $eventKey,
            'email_template_id' => $template?->id,
            'recipient_email' => $recipientEmail,
            'recipient_type' => 'system',
            'subject' => $template?->subject,
            'status' => 'skipped',
            'error_message' => $reason,
            'payload' => $data,
        ]);
    }

    private function adminRecipients(EmailTemplate $template, EmailNotificationSetting $setting): array
    {
        $recipients = collect($template->admin_recipients ?? [])
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();

        if (empty($recipients) && filter_var($setting->reply_to_email, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = $setting->reply_to_email;
        }

        if (empty($recipients) && filter_var($setting->from_email, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = $setting->from_email;
        }

        return array_values(array_unique($recipients));
    }

    private function applyMailConfig(EmailNotificationSetting $setting): void
    {
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $setting->smtp_host);
        Config::set('mail.mailers.smtp.port', $setting->smtp_port ?: 587);
        Config::set('mail.mailers.smtp.encryption', $setting->smtp_encryption ?: null);
        Config::set('mail.mailers.smtp.username', $setting->smtp_username);
        Config::set('mail.mailers.smtp.password', $setting->getDecryptedSmtpPassword());
        Config::set('mail.from.address', $setting->from_email ?: config('mail.from.address'));
        Config::set('mail.from.name', $setting->from_name ?: config('app.name'));
    }

    private function renderHtml(?EmailTemplate $template, array $data): string
    {
        $heading = $this->replaceVariables($template?->heading ?: $template?->name ?: 'Mecarvi Embroidery', $data);
        $body = $this->textToHtml($this->replaceVariables($template?->body_text ?: $template?->body_html ?: '', $data));
        $buttonText = trim($this->replaceVariables($template?->button_text ?: '', $data));
        $buttonUrl = trim($this->replaceVariables($template?->button_url ?: '', $data));
        $footerText = $this->replaceVariables($template?->footer_text ?: 'Mecarvi Embroidery', $data);
        $button = '';

        if ($buttonText !== '' && $buttonUrl !== '') {
            $safeUrl = e($buttonUrl);
            $button = "<p style=\"margin:28px 0;\"><a href=\"{$safeUrl}\" style=\"display:inline-block;background:#111827;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:700;\">".e($buttonText)."</a></p>";
        }

        return '<!doctype html><html><body style="margin:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">'
            . '<div style="display:none;max-height:0;overflow:hidden;">'.e($template?->preview_text ?: '').'</div>'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;padding:32px 16px;"><tr><td align="center">'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">'
            . '<tr><td style="padding:28px 32px;border-bottom:1px solid #e5e7eb;"><div style="font-size:16px;font-weight:800;color:#111827;">Mecarvi Embroidery</div></td></tr>'
            . '<tr><td style="padding:32px;"><h1 style="margin:0 0 18px;font-size:24px;line-height:1.25;color:#111827;">'.e($heading).'</h1>'
            . '<div style="font-size:15px;line-height:1.7;color:#374151;">'.$body.'</div>'.$button.'</td></tr>'
            . '<tr><td style="padding:22px 32px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:12px;line-height:1.5;color:#6b7280;">'.e($footerText).'</td></tr>'
            . '</table></td></tr></table></body></html>';
    }

    private function textToHtml(string $text): string
    {
        $paragraphs = preg_split("/\R{2,}/", trim($text));

        return collect($paragraphs ?: [])
            ->filter(fn ($paragraph) => trim($paragraph) !== '')
            ->map(fn ($paragraph) => '<p style="margin:0 0 16px;">' . nl2br(e(trim($paragraph))) . '</p>')
            ->implode('');
    }

    public function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
            $content = str_replace('{{ ' . $key . ' }}', (string) $value, $content);
        }

        return $content;
    }
}
