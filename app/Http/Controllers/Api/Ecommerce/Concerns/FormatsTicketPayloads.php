<?php

namespace App\Http\Controllers\Api\Ecommerce\Concerns;

use App\Models\EcommerceTicket;
use App\Models\EcommerceTicketAttachment;
use App\Models\EcommerceTicketNote;
use Illuminate\Support\Facades\Storage;

trait FormatsTicketPayloads
{
    protected function detailRelations(): array
    {
        return [
            'user:id,name,email',
            'product:id,name,sku',
            'replies.user:id,name,email',
            'replies.attachments',
            'attachments.user:id,name,email',
            'activities.user:id,name,email',
            'notes.user:id,name,email',
        ];
    }

    protected function ticketPayload(EcommerceTicket $ticket, bool $detailed = false): array
    {
        $ticket->loadMissing(['user:id,name,email', 'product:id,name,sku']);

        $payload = [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'customer_name' => $ticket->customer_name ?: $ticket->user?->name,
            'user' => $ticket->user ? [
                'name' => $ticket->user->name,
                'email' => $ticket->user->email,
            ] : null,
            'product_id' => $ticket->product_id,
            'product' => $ticket->product ? [
                'id' => $ticket->product->id,
                'name' => $ticket->product->name,
                'sku' => $ticket->product->sku,
            ] : null,
            'subject' => $ticket->subject,
            'title' => $ticket->subject,
            'message' => $ticket->message,
            'category' => $ticket->category,
            'priority' => str((string) ($ticket->priority ?: 'normal'))->replace('_', ' ')->title()->toString(),
            'status' => str((string) ($ticket->status ?: 'open'))->replace('_', ' ')->title()->toString(),
            'status_key' => strtolower(str_replace([' ', '-'], '_', (string) ($ticket->status ?: 'open'))),
            'is_urgent' => (bool) $ticket->is_urgent,
            'contact_email' => $ticket->contact_email,
            'contact_phone' => $ticket->contact_phone,
            'preferred_contact_method' => $ticket->preferred_contact_method,
            'created_at' => optional($ticket->created_at)->toISOString(),
            'updated_at' => optional($ticket->updated_at)->toISOString(),
            'closed_at' => optional($ticket->closed_at)->toISOString(),
            'messages_count' => (int) ($ticket->replies_count ?? $ticket->replies?->count() ?? 0),
            'attachments_count' => (int) ($ticket->attachments_count ?? $ticket->attachments?->count() ?? 0),
        ];

        if ($detailed) {
            $payload['replies'] = $ticket->replies->map(fn ($reply) => [
                'id' => $reply->id,
                'message' => $reply->message,
                'admin_reply' => (bool) $reply->admin_reply,
                'created_at' => optional($reply->created_at)->toISOString(),
                'user' => $reply->user ? [
                    'name' => $reply->user->name,
                    'email' => $reply->user->email,
                ] : null,
                'attachments' => $reply->attachments->map(fn ($attachment) => $this->attachmentPayload($attachment)),
            ])->values();
            $payload['attachments'] = $ticket->attachments->map(fn ($attachment) => $this->attachmentPayload($attachment))->values();
            $payload['activities'] = $ticket->activities->sortByDesc('created_at')->map(fn ($activity) => [
                'id' => $activity->id,
                'type' => $activity->type,
                'title' => $activity->title,
                'description' => $activity->description,
                'created_at' => optional($activity->created_at)->toISOString(),
                'user' => $activity->user ? [
                    'name' => $activity->user->name,
                    'email' => $activity->user->email,
                ] : null,
            ])->values();
            $notes = $this->includeInternalTicketNotes()
                ? $ticket->notes
                : $ticket->notes->where('visibility', 'public');

            $payload['notes'] = $notes
                ->sortByDesc('created_at')
                ->map(fn ($note) => $this->notePayload($note))
                ->values();
        }

        return $payload;
    }

    protected function attachmentPayload(EcommerceTicketAttachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'name' => $attachment->original_name,
            'size' => $attachment->size,
            'mime_type' => $attachment->mime_type,
            'url' => Storage::disk('public')->url($attachment->path),
            'created_at' => optional($attachment->created_at)->toISOString(),
            'author' => $attachment->user?->name,
        ];
    }

    protected function notePayload(EcommerceTicketNote $note): array
    {
        return [
            'id' => $note->id,
            'note' => $note->note,
            'visibility' => $note->visibility,
            'created_at' => optional($note->created_at)->toISOString(),
            'user' => $note->user ? [
                'name' => $note->user->name,
                'email' => $note->user->email,
            ] : null,
        ];
    }

    protected function includeInternalTicketNotes(): bool
    {
        return false;
    }
}
