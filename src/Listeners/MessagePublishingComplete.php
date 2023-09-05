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

        \Log::debug(__METHOD__, ["Message {$event->message->event_id} published successfully"]);
    }
}
