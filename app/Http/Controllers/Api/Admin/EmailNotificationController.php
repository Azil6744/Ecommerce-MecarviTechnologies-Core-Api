<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailNotificationLog;
use App\Models\EmailNotificationSetting;
use App\Models\EmailTemplate;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmailNotificationController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emails) {}

    public function overview()
    {
        $this->emails->ensureDefaultTemplates();

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => $this->emails->setting(),
                'events' => collect(EmailNotificationService::EVENTS)->map(fn ($definition, $eventKey) => [
                    'event_key' => $eventKey,
                    'label' => $definition['label'],
                    'category' => $definition['category'],
                    'variables' => $definition['variables'],
                ])->values(),
                'templates' => EmailTemplate::whereNotNull('event_key')->orderBy('category')->orderBy('name')->get(),
                'recent_logs' => EmailNotificationLog::latest()->limit(20)->get(),
            ],
        ]);
    }

    public function settings()
    {
        return response()->json([
            'success' => true,
            'data' => ['settings' => $this->emails->setting()],
        ]);
    }

    public function saveSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'is_enabled' => ['boolean'],
                'smtp_host' => ['nullable', 'string', 'max:255'],
                'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
                'smtp_username' => ['nullable', 'string', 'max:255'],
                'smtp_password' => ['nullable', 'string'],
                'smtp_encryption' => ['nullable', Rule::in(['', 'none', 'tls', 'ssl'])],
                'from_name' => ['nullable', 'string', 'max:255'],
                'from_email' => ['nullable', 'email', 'max:255'],
                'reply_to_email' => ['nullable', 'email', 'max:255'],
                'reply_to_name' => ['nullable', 'string', 'max:255'],
            ]);

            if (($validated['smtp_encryption'] ?? null) === 'none' || ($validated['smtp_encryption'] ?? null) === '') {
                $validated['smtp_encryption'] = null;
            }

            $settings = $this->emails->setting();
            if (! array_key_exists('smtp_password', $validated) || $validated['smtp_password'] === null || $validated['smtp_password'] === '') {
                unset($validated['smtp_password']);
            }
            $settings->update(array_merge(['mailer' => 'smtp'], $validated));

            return response()->json([
                'success' => true,
                'message' => 'Email settings saved.',
                'data' => ['settings' => $settings->fresh()],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        }
    }

    public function templates()
    {
        $this->emails->ensureDefaultTemplates();

        return response()->json([
            'success' => true,
            'data' => ['templates' => EmailTemplate::whereNotNull('event_key')->orderBy('category')->orderBy('name')->get()],
        ]);
    }

    public function updateTemplate(Request $request, EmailTemplate $template)
    {
        try {
            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'subject' => ['nullable', 'string', 'max:255'],
                'heading' => ['nullable', 'string', 'max:255'],
                'body_text' => ['nullable', 'string'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'button_url' => ['nullable', 'string', 'max:1000'],
                'footer_text' => ['nullable', 'string'],
                'status' => ['sometimes', Rule::in(['draft', 'published'])],
                'send_to_customer' => ['boolean'],
                'send_to_admin' => ['boolean'],
                'admin_recipients' => ['nullable', 'array'],
                'admin_recipients.*' => ['nullable', 'email', 'max:255'],
            ]);

            if (array_key_exists('admin_recipients', $validated)) {
                $validated['admin_recipients'] = collect($validated['admin_recipients'])->filter()->values()->all();
            }

            $template->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Template saved.',
                'data' => ['template' => $template->fresh()],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        }
    }

    public function restoreTemplate(EmailTemplate $template)
    {
        $definition = EmailNotificationService::EVENTS[$template->event_key] ?? null;
        if (! $definition) {
            return response()->json(['success' => false, 'message' => 'Template event is not supported.'], 422);
        }

        $template->update([
            'name' => $definition['label'],
            'subject' => $definition['subject'],
            'category' => $definition['category'],
            'heading' => $definition['heading'],
            'body_text' => $definition['body_text'],
            'button_text' => $definition['button_text'] ?? null,
            'button_url' => $definition['button_url'] ?? null,
            'footer_text' => 'Mecarvi Embroidery',
            'status' => 'published',
            'variables' => $definition['variables'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template restored.',
            'data' => ['template' => $template->fresh()],
        ]);
    }

    public function test(Request $request)
    {
        $validated = $request->validate([
            'recipient_email' => ['required', 'email', 'max:255'],
            'event_key' => ['nullable', 'string', 'max:255'],
        ]);

        $log = $this->emails->sendTest($validated['recipient_email'], [
            'event_key' => $validated['event_key'] ?? 'order_placed',
        ]);

        return response()->json([
            'success' => $log->status === 'sent',
            'message' => $log->status === 'sent' ? 'Test email sent.' : 'Test email failed.',
            'data' => ['log' => $log],
        ], $log->status === 'sent' ? 200 : 422);
    }

    public function logs(Request $request)
    {
        $query = EmailNotificationLog::query()->with('template:id,name,event_key');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('event_key')) {
            $query->where('event_key', $request->input('event_key'));
        }
        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('recipient_email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate(min((int) $request->get('per_page', 25), 100)),
        ]);
    }

    public function retry(EmailNotificationLog $log)
    {
        if (! $log->email_template_id || ! $log->template) {
            return response()->json(['success' => false, 'message' => 'Original template is missing.'], 422);
        }

        $newLog = $this->emails->sendEvent($log->template->event_key ?: Str::before($log->event_key, ':'), $log->payload ?? [], $log->recipient_email);

        return response()->json([
            'success' => true,
            'message' => 'Retry queued.',
            'data' => ['logs' => $newLog],
        ]);
    }
}
