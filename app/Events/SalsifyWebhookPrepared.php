<?php

namespace App\Events;

use App\Models\Salsify\Webhook;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalsifyWebhookPrepared
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Webhook
     */
    public $webhook;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Webhook $webhook)
    {
        //
        $this->webhook = $webhook;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
