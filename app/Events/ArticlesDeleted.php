<?php

namespace App\Events;

use App\Models\Articles;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 
 */
class ArticlesDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $articles;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Articles $articles)
    {
        $this->$articles = $articles;
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
