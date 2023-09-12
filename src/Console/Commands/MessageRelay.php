<?php

namespace ShowersAndBs\TransactionalOutbox\Console\Commands;

use Illuminate\Console\Command;
use ShowersAndBs\TransactionalOutbox\Events\PublishingRun;
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
        $this->info("RELAY: Start dispatching messages from transactional outbox table...");

        $run = [$this, 'dispatchPendingMessages'];

        // message relay deamon
        while (true) {
            $pause = 1000 * 1000; // wait 1s between cycles

            try {

                $run();

            } catch (\Throwable $e) {
                // In case of any error, for example the database is not available
                // Do not interrupt the operation of the daemon relay
                \Log::error(__METHOD__, [$e->getMessage()]);
                report($e);
                $pause = 10 * 1000 * 1000; // wait 10s for next cycle, so as not to be overwhelmed by error messages
            }

            usleep($pause);
        }
    }

    /**
     * Dispatch pending messages from outboc to the message broker
     *
     * @return void
     */
    public function dispatchPendingMessages()
    {
        $messagesToPublish = (new OutgoingMessage)->getPendingMessages();

        if($messagesToPublish->count() === 0) {
            return;
        }

        $messagesToPublish
            ->each(function ($message) {
                $message->setSending();
                PublishingRun::dispatch($message);
            });
    }

    /**
     * Write a string as standard output and log.
     *
     * @param  string  $string
     * @param  string|null  $style
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function line($string, $style = null, $verbosity = null)
    {
        parent::line($string, $style, $verbosity);

        \Illuminate\Support\Facades\Log::info($string);
    }
}
