<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $uploadId;
    public $status;

    public function __construct($uploadId, $status)
    {
        $this->uploadId = $uploadId;
        $this->status = $status;
    }

    public function broadcastOn()
    {
        return new Channel('uploads');
    }

    public function broadcastAs()
    {
        return 'upload.status.updated';
    }

    public function broadcastWith()
    {
        return [
            'uploadId' => $this->uploadId,
            'status' => $this->status,
        ];
    }
}

