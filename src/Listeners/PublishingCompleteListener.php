<?php

namespace ShowersAndBs\TransactionalOutbox\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use ShowersAndBs\TransactionalOutbox\Events\PublishingComplete;

class PublishingCompleteListener
{
    /**
     * Handle the event.
     */
    public function handle(PublishingComplete $event): void
    {
        $event->message->setPublished();

        \Log::debug("RELAY: {$event->message->event}:{$event->message->event_id} published successfully");
    }
}
