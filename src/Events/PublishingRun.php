<?php

namespace ShowersAndBs\TransactionalOutbox\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ShowersAndBs\TransactionalOutbox\Models\OutgoingMessage;

class PublishingRun
{
    use Dispatchable, SerializesModels;

    /**
     * The outgoing message.
     *
     * @var \ShowersAndBs\TransactionalOutbox\Models\OutgoingMessage
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param  \ShowersAndBs\TransactionalOutbox\Models\OutgoingMessage $OutgoingMessage
     * @return void
     */
    public function __construct(OutgoingMessage $message)
    {
        $this->message = $message;
    }
}
