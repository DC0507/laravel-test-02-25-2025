<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DrupalRecipeCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * array of recipe data that was created.
     *
     * @var array
     */
    public $recipe_data = [];

    /**
     * Create a new event instance.
     * @param array $recipe_data
     * @return void
     */
    public function __construct($recipe_data)
    {
        $this->recipe_data = $recipe_data;
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
