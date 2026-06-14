<?php

namespace App\Traits;

use App\Events\ContentUpdated;
use Illuminate\Support\Facades\Cache;

trait BroadcastsContentUpdates
{
    /**
     * Broadcast content update event
     *
     * @param string $contentType
     * @param string $action (created, updated, deleted)
     * @param mixed $data
     * @return void
     */
    protected function broadcastContentUpdate($contentType, $action, $data = null)
    {
        Cache::forget('public_home_payload_v1');
        event(new ContentUpdated($contentType, $action, $data));
    }
}

