<?php

namespace ShowersAndBs\TransactionalOutbox\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MessagePublishingComplete
{
    /**
     * Handle the event.
     */
    public function handle(\ShowersAndBs\TransactionalOutbox\Events\MessagePublishingComplete $event): void
    {
        $event->message->setPublished();

        \Log::debug("RELAY: {$event->message->event}:{$event->message->event_id} published successfully");
    }
}
