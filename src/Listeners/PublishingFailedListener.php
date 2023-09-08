<?php

namespace ShowersAndBs\TransactionalOutbox\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use ShowersAndBs\TransactionalOutbox\Events\PublishingFailed;

class PublishingFailedListener
{
    /**
     * Handle the event.
     */
    public function handle(PublishingFailed $event): void
    {
        $event->message->setFailed();

        \Log::debug("RELAY: {$event->message->event}:{$event->message->event_id} publishing fail");
    }
}
