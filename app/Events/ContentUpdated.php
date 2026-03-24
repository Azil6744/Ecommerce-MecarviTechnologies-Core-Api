<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $contentType;
    public $action; // 'created', 'updated', 'deleted'
    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct($contentType, $action, $data = null)
    {
        $this->contentType = $contentType; // e.g., 'home-page', 'about-section', 'service-card'
        $this->action = $action; // 'created', 'updated', 'deleted'
        $this->data = $data;
        
        // Update global content update timestamp for polling
        cache()->forever('content_updated_at', now()->toIso8601String());
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('content-updates'), // Public channel - no authentication required
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'content.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'content_type' => $this->contentType,
            'action' => $this->action,
            'data' => $this->data,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
