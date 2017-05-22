<?php

namespace App\Http\Controllers;

use App\Message;
use App\Notification;
use App\Thread;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class MessageController extends Controller
{
    protected $thread;
    protected $notification;
    protected $message;

    public function __construct(Thread $thread, Notification $notification, Message $message)
    {
        $this->thread       = $thread;
        $this->notification = $notification;
        $this->message      = $message;
    }

    public function sendMessage(Request $request)
    {
        $conversation['sender']    = Auth::id();
        $conversation['recipient'] = (int)$request->recipientId;
        $message['messageBody']           = $request->newMessage;

        $thread = $this->getThread($conversation);

        $messageId    = $this->saveMessage($message);
        $notification = $this->createNotification($thread, $messageId);

        return redirect(route('message.retrieveMessages',['senderId'=>$thread->senderId, 'recipientId'=>$thread->recipientId]));


    }

    public function retrieveMessages(Request $request)
    {
        $messages = [];
        $recipient = $request['recipientId'];
        $thread = $this->thread->where([
            ['senderId', '=', Auth::id()],
            ['recipientId', '=', $request['recipientId']]])
            ->orWhere([
                ['senderId', '=', $request['recipientId']],
                ['recipientId', '=', Auth::id()],
            ])->get();
        if ($thread->count()>0) {
            $notifications = [];
            array_push($notifications, $thread[0]->visibleNotifications(Auth::id())->get());
            array_push($notifications, $thread[1]->visibleNotifications(Auth::id())->get());

            foreach ($notifications as $notification) {
                collect($notification)->map(function ($n) {
                    return $n->message;
                });
            }
            foreach ($notifications as $notification) {
                foreach ($notification as $n) {
                    $data['sender']    = ['id' => $n->thread['senderId'], 'name' => User::find($n->thread['senderId'])->name];
                    $data['recipient'] = ['id' => $n->thread['recipientId'], 'name' => User::find($n->thread['recipientId'])->name];
                    $data['messageId']   = $n->message['id'];
                    $data['message']   = $n->message['messageBody'];
                    $data['time']      = $n->message['created_at']->todatetimestring();
                    array_push($messages, $data);
                }
            }
            $this->sortBy('time', $messages);
        }

        return view('messages.showMessages')->with('messages', $messages)->with('recipient', $recipient);

    }

    function sortBy($field, &$array, $direction = 'asc')
    {
        usort($array, create_function('$a, $b', '
        $a = $a["'.$field.'"];
        $b = $b["'.$field.'"];
        if ($a == $b)
        {
            return 0;
        }
        return ($a '.($direction == 'desc' ? '>' : '<').' $b) ? -1 : 1;
 '));

        return true;
    }

    public function viewThreads()
    {
        $user = Auth::user();
        $threads = $user->threads;
        collect($threads)->map(function($thread){
            return $thread->user;
        });

        return view('messages.viewThreads')->with('threads', $threads);
    }

    public function destroyMessage(Request $request)
    {
        $deleteStatus = $this->notification->where([
            ['messageId', '=', $request->messageId],
            ['visibility', '=', Auth::id()]
        ])->delete();
        if($deleteStatus) {
            return Redirect::back();
        } else{
            return view('unauthorizedAccess')->with('response','Whoops!! Something went wrong!!');
        }
    }

    public function deleteThread()
    {

    }

//    public function threadExists($conversation)
//    {
//        $threadId = $this->thread->where('senderId', $conversation['sender'])
//                                ->where('recipientId', $conversation['recipient'])->first;
//        return $threadId;
//    }

    public function getThread($conversation)
    {
//        $threadId = $this->threadExists($conversation);
//        if(!$threadId){
//            $threadId = $this->createThread($conversation);
//        }
        $anotherThread = $this->thread->firstOrCreate(['senderId' => $conversation['recipient'], 'recipientId' => $conversation['sender']]);
        $thread = $this->thread->firstOrCreate(['senderId' => $conversation['sender'], 'recipientId' => $conversation['recipient']]);

        return ($thread);
    }

//    public function createThread($conversation)
//    {
//        $this->thread->create
//    }

    public function createNotification($thread, $messageId)
    {
        $data['threadId']      = $thread->id;
        $data['messageId']     = $messageId;
        $data['visibility']    = $thread->senderId;
        $notification['first'] = $this->notification->create($data);

        $data['visibility']     = $thread->recipientId;
        $notification['second'] = $this->notification->create($data);

        return ($notification);
    }

    public function saveMessage($message)
    {
        $savedMessage = $this->message->create($message);
        return $savedMessage->id;
    }
}
