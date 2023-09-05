<?php

namespace ShowersAndBs\TransactionalOutbox\Listeners;

use Illuminate\Events\Dispatcher;
use ShowersAndBs\TransactionalOutbox\Contracts\ShouldBePublished;
use ShowersAndBs\TransactionalOutbox\Models\OutgoingMessage;

class PublishableEventSubscriber
{
    /**
     * Handle only publishable events.
     */
    public function handle(string $eventClass, $payload): void
    {
        if (! $this->shouldBePublished($eventClass)) {
            return;
        }

        $eventInstance = $payload[0];

        (new OutgoingMessage)->persistEvent($eventInstance);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen('*', static::class . '@handle');
    }

    /**
     * If the event implement ShouldBePublished interface
     *
     * @param  string $eventClass
     * @return bool
     */
    private function shouldBePublished(string $eventClass): bool
    {
        if (! class_exists($eventClass)) {
            return false;
        }

        return is_subclass_of($eventClass, ShouldBePublished::class);
    }
}
