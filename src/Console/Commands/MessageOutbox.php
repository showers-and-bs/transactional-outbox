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
                            {--stat : Display statistics}
                            {--id= : Display message with the given id}
                            {--event-id= : Display messages with the given event_id}
                            {--resend : Change message status to 0:PENDING, the message is selected by the options --id or --event-id}
                            {--status= : Display messages with the given status}
                            {--event= : Display messages with the given event, the event name can be shorten}
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
        $stat = $this->option('stat') ?? false;
        $id = $this->option('id');
        $eventId = $this->option('event-id');
        $resend = $this->option('resend') ?? false;
        $noLimit = $this->option('no-limit') ?? false;
        $limit = $this->option('limit');

        if($stat) {
            $this->showStatistics();
            return;
        }

        if($id && $eventId) {
            $this->error('Only one of these two, --id or --event-id, can be provided');
            return;
        }

        if($eventId) {
            try {
                $message = $this->getMessageForGivenEventId($eventId);
                $id = $message->id;
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                return;
            }
        }

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

            $this->info("Status of the message id:{$message->id} changed to PENDING");

        } catch(\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Display statistical data
     *
     * @return void
     */
    private function showStatistics()
    {
        $this->line('Today');
        $this->showStatisticsToday();

        $this->newLine();

        $this->line('Overall');
        $this->showStatisticsAllTime();
    }

    /**
     * Display statistical data
     *
     * @return void
     */
    private function showStatisticsAllTime()
    {
        $groupByStatus = OutgoingMessage::query()
            ->selectRaw('status, count(status) as count')
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(function($item) {
                return [
                    $item->status . '|' . $this->statusMap[$item->status],
                    $item->count,
                ];
            });

        $this->table(
            ['status', 'count'],
            $groupByStatus
        );
    }

    /**
     * Display statistical data for today
     *
     * @return void
     */
    private function showStatisticsToday()
    {
        $groupByStatus = OutgoingMessage::query()
            ->selectRaw('status, count(status) as count')
            ->whereDate('created_at', date('Y-m-d'))
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(function($item) {
                return [
                    $item->status . '|' . $this->statusMap[$item->status],
                    $item->count,
                ];
            });

        $this->table(
            ['status', 'count'],
            $groupByStatus
        );
    }

    /**
     * Ge
     *
     * @param  string $eventId [description]
     * @return [type]          [description]
     */
    public function getMessageForGivenEventId(string $eventId)
    {
        return OutgoingMessage::query()
            ->where('event_id', $eventId)
            ->firstOrFail();
    }
}
