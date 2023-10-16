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
                            {--resend : Change message status to 0:PENDING}
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
        $resend = $this->option('resend') ?? false;
        $noLimit = $this->option('no-limit') ?? false;
        $limit = $this->option('limit');

        if ($id) {
            if(! is_numeric($id)) {
                $this->error('Option --id must be integer');
                return;
            }

            $this->showMessage($id);

            if ($resend) {
                $this->resendMessage($id);
                return;
            }

            return;
        }

        if ($resend && is_null($id)) {
            $this->error('Option --id must be set');
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
     * Display message details
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

    /**
     * Resend the message by changing status to PENDING
     *
     * @return void
     */
    private function resendMessage(int $id)
    {
        try {
            $message = OutgoingMessage::find($id);

            if(is_null($message) || $message->status === OutgoingMessage::PENDING) {
                return;
            }

            $message->status = OutgoingMessage::PENDING;
            $message->save();

        } catch(\Exception $e) {
            $this->error($e->getMessage());
        }
    }

}
