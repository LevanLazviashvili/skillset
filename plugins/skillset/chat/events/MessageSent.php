<?php namespace skillset\Chat\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use RainLab\User\Models\User;
use skillset\Chat\Models\Chat;

class MessageSent implements ShouldBroadcast
{
//    public $user;
    public $chat;


    public function __construct(Chat $chat)
    {
        $this->chat = $chat->toArray();

    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat');
    }


}