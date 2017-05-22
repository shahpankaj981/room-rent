@extends('layouts.app')

@section('content')
  <style>
    .participantName {
      color: blue;
    }
    .deleteBtn{
      margin:80px;
    }

    .message {
      color: black !important;
    }
  </style>
  <div class="container">
    @if($messages)
      @foreach($messages as $message)
        <div class="participantName">
          <a href="{{ route('room.profile', ['userId' => $message['sender']['id'] ]) }}">
            {{ $message['sender']['name'] }}
          </a>
        </div>

        <div class="message"
             style="color: black; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 19px">
          <span>{{ $message['message'] }}</span>
          <span style="margin-left:40px; font-family: 'Open Sans', sans-serif; font-size: 12px;">
            <a href="{{route('message.destroy',['messageId'=>$message['messageId']])}}"><button class="deleteBtn">Delete</button>
            </a> </span>
        </div>

        <div class="time">
          On {{ $message['time'] }}
        </div>

        <hr style="color: blue">
      @endforeach
    @endif

    <div class="row">
      <div class="col-md-8">
        <div class="panel panel-default">
          <div class="panel-body">

            {!! Form::open(['route'=>['message.sendMessage','recipientId'=> $recipient]]) !!}
            <div class="form-group">
              {!! Form::textarea('newMessage', '', ['required','autofocus', 'class' => 'form-control']) !!}
            </div>
            <div>
              {!! Form::submit('SendMessage') !!}
            </div>
            {!! Form::close() !!}
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection