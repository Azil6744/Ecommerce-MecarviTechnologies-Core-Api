<?php

namespace App\Traits;

use App\Events\ContentUpdated;

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
        event(new ContentUpdated($contentType, $action, $data));
    }
}

