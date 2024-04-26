<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class turno implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $registroid;
    public $userid;
    public $horizontal;
    public $vertical;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($registroid,$userid,$horizontal,$vertical,)
    {
      
        $this->registroid=$registroid;
        $this->userid=$userid;
        $this->horizontal=$horizontal;
        $this->vertical=$vertical;
 
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('movimiento');
    }
}
