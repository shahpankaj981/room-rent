@extends('layouts.app')

@section('content')

  <style>
    img {
      border: 2px solid #1F1F1F;
    }
  </style>


  <div class="container">
    @if(Session::has('flash_message'))
      <div class="alert alert-success"> {{ Session::get('flash_message') }} </div>
    @endif

    <div>
      <h3><b>{{ $user->name }}</b></h3>
    </div>
    <hr>
    <div id="profileImage">
      <img src="{{ route('file.get', ['filename' => $user->profileImage ])}}" width="200px" height=auto/>
      @if(Auth::id() == $user->id)
        <a href={{route('room.updateProfileImage', ['userId'=>$user->id])}}>
          <br>
          Edit Profile Image
        </a>
      @endif
    </div>
    <hr>
    <div>
      <h4><b>Email :</b> {{ $user->email }}</h4>
      <h4><b>Phone: </b>{{ $user->phone }} </h4>
      <br>
    </div>
    @if(Auth::id() == $user->id)
      <a href="{{ route('room.updateProfileInfo',['userId'=> $user->id]) }}">Edit Profile Info</a>
      <br>
      <a href="{{route('room.changePasswordForm', ['userId' =>$user->id]) }}">Change Password</a>
    @endif
  </div>
  <hr>
  <br>
  <h3><b>Your Posts:</b></h3>
  <hr>
  <div class="container">
    @foreach($posts as $post)
      <span id="userName">
          <h4>
            <a href="{{route('room.profile', ['userid' => $user->id])}}">{{$user->name}}</a>

          </h4>
         </span>
      <span id="postDate">
          <h4>
          {{$post['postDate']}}
          </h4>
      </span>
      @if($post['postType' == 1])
        <h4>You Asked for:</h4>
      @else
        <h4> You Offered:</h4>
      @endif
      <h4>
        <a href="{{route('room.show', ['id'=> $post['id']])}}">
          <b>{{$post['title']}}</b>
          <br>
          @if($post['images'])
            <img src="{{$post['images'][0]}}" alt="" height="250" width=auto id="postImage"/>
          @endif
        </a>
        <br>
        @if(Auth::id() == $user->id)
          <a href="{{ route('room.post.destroy',['userId'=>$user->id, 'postId' =>$post->id]) }}"> Delete</a>
        @endif
      </h4>
      <br/>
      <hr>
    @endforeach
  </div>
  </div>

@endsection

