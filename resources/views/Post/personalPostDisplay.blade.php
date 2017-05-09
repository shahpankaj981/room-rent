@extends('layouts.app')

@section('content')

  <h1>$post['user']</h1>

    <div class="col col-md-12">
      <div>
        @foreach($posts['post'] as $post)
          @if($post['postType' = "1"])
            <h3> You Asked</h3>
          else
          <h3>You Offered</h3>
          @endif
          <span id="postDate">
          <h3>
          {{$post['postDate']}}
          </h3>
         </span>

          @if($post['images'])
            <img src="{{$post['images'][0]}}" alt="" height="250" width="250" id= "postImage" />
          @endif
          <br>
          <h3>
            <a href="{{route('room.show', ['id'=> $post['id']])}}">
              {{$post['title']}}
            </a>
          </h3>
          <br/>
          <hr>
        @endforeach
      </div>
    </div>
@endsection