<?php

namespace App\Events;

use App\Models\Salsify\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalsifyProductDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Product
     */
    public $salsify_product;

    /**
     * Create a new event instance.
     *
     * @param Product $salsify_product
     * @return void
     */
    public function __construct(Product $salsify_product)
    {
        $this->salsify_product = $salsify_product;
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
