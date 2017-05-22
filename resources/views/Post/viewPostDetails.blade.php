@extends('layouts.app')
<style>
  .postImage {
    width: auto;
    height: 250px;
    border: 2px solid #121212;
  }
</style>

@section('content')

  <div>
    <h1>Post Details:</h1>
    <hr>
    <h2> {{ $post['user'][0] }}</h2>
    <h4>Posted On :{{ $post['postDate'] }}</h4>
    @if($post['images'])
      <div>
        @foreach($post['images'] as $image)
          <img src="{{$image}}" width="250" height="250" class="postImage">
        @endforeach
      </div>
    @endif
    <hr>
    <h4><b> Title : </b> {{ $post['title'] }}</h4>
    <h4><b> Location : </b>{{ $post['location']}}</h4>
    <h4><b>Price : </b> {{ $post['price'] }}</h4>
    <h4><b> Number Of Rooms : </b> {{ $post['numberOfRooms'] }}</h4>
    <h4><b>Description : </b> {{ $post['description'] }}</h4>

  </div>

@endsection