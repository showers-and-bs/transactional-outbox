<?php

namespace ShowersAndBs\TransactionalOutbox\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MessagePublishingFailed
{
    /**
     * Handle the event.
     */
    public function handle(\ShowersAndBs\TransactionalOutbox\Events\MessagePublishingFailed $event): void
    {
        $event->message->setFailed();

        \Log::debug("RELAY: {$event->message->event}:{$event->message->event_id} publishing fail");
    }
}
