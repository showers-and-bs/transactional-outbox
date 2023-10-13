<?php

namespace ShowersAndBs\TransactionalOutbox\Console\Commands;

use Illuminate\Console\Command;
use ShowersAndBs\TransactionalOutbox\Models\OutgoingMessage;

class MessageOutbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amqp:outbox {--limit=10} {--no-limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Browse messages in transactional outbox';

    /**
     * Descriptive message status
     *
     * @var array
     */
    private $statusMap = [
        OutgoingMessage::PENDING => 'PENDING',
        OutgoingMessage::SENDING => 'SENDING',
        OutgoingMessage::FAILED => 'FAILED',
        OutgoingMessage::PUBLISHED => 'PUBLISHED',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $noLimit = $this->option('no-limit');
        $limit = $this->option('limit');

        if($noLimit) {
            $this->showList();
            return;
        }

        $this->showList($limit);
    }

    /**
     * Show messages in the list
     *
     * @return void
     */
    private function showList(int $limit = null)
    {
        $messages = OutgoingMessage::query()
            ->select(['id', 'event_id', 'event', 'status'])
            ->orderBy('id', 'desc')
            ->when($limit, function($query) use ($limit) {
                $query->limit($limit);
            })
            ->get()
            ->each(fn($item) => $item->status2 = $this->statusMap[$item->status]);

        $this->table(
            ['id', 'event_id', 'event', 'status', 'descriptive status'],
            $messages->toArray()
        );
    }

}
