<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class BellRingEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets;

    public $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function broadcastOn() {
        return new Channel('bell-channel');
    }
}