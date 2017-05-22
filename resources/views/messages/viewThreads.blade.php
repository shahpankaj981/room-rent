@extends('layouts.app')

@section('content')

  <div class="container">
    <h2><b>Messages</b></h2>
    <hr>
    @forelse($threads as $thread)
      <a href="{{route('message.retrieveMessages',['recipientId'=>$thread['user']['id']])}}">
                        <span class="col-md-10"
                              style="background-color: #dedede; border-radius: 4px; margin:2px; padding-top:15px; padding-bottom: 15px; text-align:center;">
                        <b
                            style="font-size: 25px; color:black;"> {{$thread['user']['name']}}</b>
                          <br>View Messages
                        </span>
      </a>
      <hr style="color:blue">
    @empty
      <p>No messages to display</p>
    @endforelse
  </div>

@endsection