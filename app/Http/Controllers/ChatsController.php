<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Http\Requests\DisableChatRequest;
use App\Http\Requests\EnableChatRequest;
use App\Http\Requests\ReadChatRequest;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Mail\ChatTopicCreated;
use App\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ChatsController extends Controller
{

    public function index()
    {
        $chats = Chat::where('status', Chat::ACTIVE)->orderBy('id', 'DESC')->get();
        return view('chat.index', compact('chats'));
    }

    public function create()
    {
        $orders = Order::all();
        return view('chat.create', compact('orders'));
    }

    public function store(StoreChatRequest $request)
    {
        $chat = Chat::create($request->only('topic', 'order_id') + ['user_id' => Auth::id()]);
        $chat->messages()->create($request->only('message') + ['user_id' => Auth::id()]);

        if(Auth::user()->role == 'user'){
            $adminEmail = Auth::user()->country->email;
            Mail::to($adminEmail)->send(new ChatTopicCreated());
        }

        return redirect()->route('chat.show', $chat->id);
    }

    public function show(ReadChatRequest $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $messages = $chat->messages()->get();
        return view('chat/show', compact('chat', 'messages'));
    }

    public function storeMessage(StoreMessageRequest $request)
    {
        $chat = Chat::findOrFail($request->chat_id);

        if (Auth::user()->role == 'admin') {
            if ($chat->admin_id === null) {
                $chat->update(['admin_id' => Auth::id()]);
            }
        }
        dd($chat->user->country->users()->where('role','admin')->get());
        $chat->messages()->create($request->only('message') + ['user_id' => Auth::id()]);

        $user = $chat->user;
        $admin = $chat->user->country;

        $userTrack = $user->user_online;
        $userRole = $user->role;
        $userEmail = $user->client->email;

        $adminTrack = $user->
        $adminRole = $admin->role;
        $adminEmail = $admin->email;

        $diffTime = carbon::now()->diffInMinutes($userTrack);
        if($userRole == 'user' && $diffTime > 5){
            Mail::to($userEmail)->send(new ChatTopicCreated());
        } elseif($adminRole == 'admin' && $diffTime)

        return redirect()->route('chat.show', $request->chat_id);
    }

    public function disable(DisableChatRequest $request)
    {
        $chat = Chat::findOrFail($request->chat_id);
        $chat->update(['status' => Chat::INACTIVE]);
        return redirect()->back();
    }

    public function enable(EnableChatRequest $request)
    {
        $chat = Chat::findOrFail($request->chat_id);
        $chat->update(['status' => Chat::ACTIVE]);
        return redirect()->back();
    }

    public function getUserChats()
    {
        Chat::where('user_id', Auth::id())->get();
    }
}
