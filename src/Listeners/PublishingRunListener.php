<?php

namespace ShowersAndBs\TransactionalOutbox\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use ShowersAndBs\TransactionalOutbox\Events\PublishingRun;
use ShowersAndBs\TransactionalOutbox\Jobs\PublishMessage;

class PublishingRunListener
{
    /**
     * Handle the event.
     */
    public function handle(PublishingRun $event): void
    {
        PublishMessage::dispatch($event->message);

        \Log::debug("RELAY: {$event->message->event}:{$event->message->event_id} ready for publishing");
    }
}
