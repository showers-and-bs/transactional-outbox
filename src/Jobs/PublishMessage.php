<?php

namespace ShowersAndBs\TransactionalOutbox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ShowersAndBs\TransactionalOutbox\Events\MessagePublishingComplete;
use ShowersAndBs\TransactionalOutbox\Events\MessagePublishingFailed;

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
            \Log::info(__METHOD__, ["Message {$this->message->event_id} has been published"]);
            return;
        }

        $message = $this->message->only(['event_id','event','payload']);

        $content = new \Anik\Amqp\ProducibleMessage(json_encode($message));
        $routeKey = '';
        $exchange = new \Anik\Amqp\Exchanges\Fanout('amq.fanout');
        $options = [];

        try {

            \Anik\Laravel\Amqp\Facades\Amqp::getProducer()->publishBasic($content, $routeKey, $exchange, $options);

        } catch (\Throwable $e) {

            MessagePublishingFailed::dispatch($this->message);

            throw new \Exception($e->getMessage(), 1);
        }

        MessagePublishingComplete::dispatch($this->message);
    }
}
