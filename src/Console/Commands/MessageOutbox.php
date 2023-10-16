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
    protected $signature = 'amqp:outbox
                            {--id= : Display message with given id}
                            {--limit=10 : Display limited number of messages}
                            {--no-limit : Display all messages}';

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
        $id = $this->option('id');
        $noLimit = $this->option('no-limit') ?? false;
        $limit = $this->option('limit');

        if ($id) {
            $this->showMessage($id);
            return;
        }

        if ($noLimit) {
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
            ->select(['id', 'created_at', 'updated_at', 'event_id', 'event', 'status'])
            ->orderBy('id', 'desc')
            ->when($limit, function($query) use ($limit) {
                $query->limit($limit);
            })
            ->get()
            ->map(function($item) {
                return [
                    $item->id,
                    date('Y-m-d H:i:s', strtotime($item->created_at)),
                    date('Y-m-d H:i:s', strtotime($item->updated_at)),
                    $item->event_id,
                    $item->event,
                    $item->status . '|' . $this->statusMap[$item->status],
                ];
            });

        $this->table(
            ['id', 'created_at', 'updated_at', 'event_id', 'event', 'status'],
            $messages
        );
    }

    /**
     * Show messages in the list
     *
     * @return void
     */
    private function showMessage(int $id)
    {
        try {
            $message = OutgoingMessage::findOrFail($id);

            $output = [
                ['id', $message->id],
                ['created_at', date('Y-m-d H:i:s', strtotime($message->created_at))],
                ['updated_at', date('Y-m-d H:i:s', strtotime($message->updated_at))],
                ['event_id', $message->event_id],
                ['event', $message->event],
                ['payload', unserialize($message->payload)],
                ['status', $message->status . '|' . $this->statusMap[$message->status]],
            ];

            $this->table(
                ['property', 'value'],
                $output
            );
        } catch(\Exception $e) {
            $this->error($e->getMessage());
        }
    }

}
