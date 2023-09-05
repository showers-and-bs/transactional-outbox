<?php

namespace ShowersAndBs\TransactionalOutbox\Contracts;

interface ShouldBePublished
{
    /**
     * Get event name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get data associated with an event, most likely a json encoded array
     *
     * @return string
     */
    public function getPayload(): string;
}
