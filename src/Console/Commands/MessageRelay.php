<?php

namespace ShowersAndBs\TransactionalOutbox\Console\Commands;

use Illuminate\Console\Command;
use ShowersAndBs\TransactionalOutbox\Events\MessagePublishingRun;
use ShowersAndBs\TransactionalOutbox\Models\OutgoingMessage;

class MessageRelay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amqp:relay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Relay messages from transactional outbox';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // dispatch messages for publishing
        $run = function() {

            $messagesToPublish = (new OutgoingMessage)->getPendingMessages();

            if($messagesToPublish->count() === 0) {
                return;
            }

            $messagesToPublish
                ->each(function ($message) {
                    $message->setSending();
                    MessagePublishingRun::dispatch($message);
                });
        };

        // main
        while (true) {
            $pause = 1000 * 1000; // wait 1s between cycles

            try {

                $run();

            } catch (\Throwable $e) {
                // In case of any error, for example the database is not available
                // Do not interrupt the operation of the daemon relay
                \Log::error(__METHOD__, [$e->getMessage()]);
                $pause = 10 * 1000 * 1000; // wait 10s for next cycle, so as not to be overwhelmed by error messages
            }

            usleep($pause);
        }
    }
}
