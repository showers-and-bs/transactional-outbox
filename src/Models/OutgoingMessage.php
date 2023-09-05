<?php

namespace ShowersAndBs\TransactionalOutbox\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ShowersAndBs\TransactionalOutbox\Contracts\ShouldBePublished;

class OutgoingMessage extends Model
{
    const PENDING = 0;

    const SENDING = 1;

    const FAILED = 2;

    const PUBLISHED = 3;

    /**
     * Persist an publishable event to the database
     *
     * @param  \ShowersAndBs\TransactionalOutbox\Contracts\ShouldBePublished $event
     */
    public function persistEvent(ShouldBePublished $event): void
    {
        $this->event_id = \Illuminate\Support\Str::uuid()->toString();
        $this->event    = $event->getName();
        $this->payload  = $event->getPayload();
        $this->status   = self::PENDING;

        $this->save();
    }

    public function getPendingMessages(): \Illuminate\Support\Collection
    {
        return $this->query()
            ->where('status', self::PENDING)
            ->get();
    }

    public function isPublished(): bool
    {
        return $this->status == self::PUBLISHED;
    }

    public function setSending(): void
    {
        $this->status = self::SENDING;
        $this->save();
    }

    public function setFailed(): void
    {
        $this->status = self::FAILED;
        $this->save();
    }

    public function setPublished(): void
    {
        $this->success_at = now();
        $this->status = self::PUBLISHED;
        $this->save();
    }
}
