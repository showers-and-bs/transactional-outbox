<?php

namespace ShowersAndBs\TransactionalOutbox\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MessagePublishingRun
{
    /**
     * Handle the event.
     */
    public function handle(\ShowersAndBs\TransactionalOutbox\Events\MessagePublishingRun $event): void
    {
        \ShowersAndBs\TransactionalOutbox\Jobs\PublishMessage::dispatch($event->message);

        \Log::debug("RELAY: {$event->message->event}:{$event->message->event_id} ready for publishing");
    }
}
