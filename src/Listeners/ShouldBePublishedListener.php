<?php

namespace ShowersAndBs\TransactionalOutbox\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use ShowersAndBs\ThirstyEvents\Contracts\ShouldBePublished;
use ShowersAndBs\TransactionalOutbox\Models\OutgoingMessage;

class ShouldBePublishedListener
{
    /**
     * Handle the event.
     */
    public function handle(ShouldBePublished $event): void
    {
        (new OutgoingMessage)->persistEvent($event);

        \Log::debug("RELAY: " . get_class($event) . " ready for publishing");
    }
}
