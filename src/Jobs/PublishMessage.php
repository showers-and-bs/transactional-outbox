<?php

namespace ShowersAndBs\TransactionalOutbox\Jobs;

use Anik\Amqp\Exchanges\Fanout;
use Anik\Amqp\ProducibleMessage;
use Anik\Laravel\Amqp\Facades\Amqp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ShowersAndBs\TransactionalOutbox\Events\PublishingComplete;
use ShowersAndBs\TransactionalOutbox\Events\PublishingFailed;

class PublishMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
    * Calculate the number of seconds to wait before retrying the job.
    *
    * @return array<int, int>
    */
    public function backoff(): array
    {
        return [1, 5, 10, 30, 60];
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public \ShowersAndBs\TransactionalOutbox\Models\OutgoingMessage $message)
    {
        //
    }

    /**
     * Execute the job.
     *
     * Publish a message to the message broker
     *
     * @return void
     */
    public function handle()
    {
        if($this->message->isPublished()) {
            \Log::info("RELAY: :Message {$this->message->event}:{$this->message->event_id} has been published");
            return;
        }

        $message = $this->message->only(['created_at','event_id','event','payload']);

        $messageDTO = \ShowersAndBs\ThirstyEvents\DTO\RabbitMqMessagePayload::createFromArray($message);

        $content = new ProducibleMessage($messageDTO->serialize());
        $routeKey = '';
        $exchange = new Fanout('amq.fanout');
        $options = [];

        try {

            Amqp::getProducer()->publishBasic($content, $routeKey, $exchange, $options);

        } catch (\Throwable $e) {

            PublishingFailed::dispatch($this->message);

            throw new \Exception($e->getMessage(), 1);
        }

        PublishingComplete::dispatch($this->message);
    }
}
