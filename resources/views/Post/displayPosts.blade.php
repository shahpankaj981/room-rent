@extends('layouts.app')
<style>
  #userName {
    width: 50%;
  }

  #postImage {
    clear: both;
  }

  #postDate {
    width: 50%;
  }
</style>
@section('content')
  <div class="col col-md-12">
    @if($postType = 1)
      <h1>Ask Posts</h1>
    @else
      <h1>Offer Posts</h1>
    @endif
    <div>
      @foreach($posts as $post)
        <span id="userName">
          <h3>
            <a href="{{route('room.profile', ['userId'=> $post['user']['id']])}}"><b>{{$post['user']['name']}}</b></a>
          </h3>
         </span>

        <span id="postDate">
          <h4>
          {{$post['postDate']}}
          </h4>
         </span>

        <a href="{{route('room.show', ['id'=> $post['id']])}}">
          @if($post['images'])
            <img src="{{$post['images'][0]}}" alt="" height="250" width=auto id="postImage"/>
          @endif
          <br>
          <h4>{{$post['title']}}</h4>
        </a>
        <br/>
        <hr>
      @endforeach
    </div>
  </div>
@endsection
