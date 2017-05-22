@extends('layouts.app')

@section('content')
  {{--<style>--}}
    {{--a:hover{--}}
      {{--color:red;--}}
    {{--}--}}
  {{--</style>--}}

  <h3>Search result for: <b>{{$query}}</b></h3>
  <hr>
  @forelse($data as $datum)
    <div>
      <a href="{{ route('room.profile', ['userId' => $datum['id']]) }}" style="font-size: 20px;margin-left: 15px">
        <span class="image" style="height: 75px; width:100px;">
          <img src="{{ route('file.get',['filename'=> $datum['image']]) }}" height="75"
               style="border: 1px solid gray; border-radius: 4px;">
        </span>
        <span style="margin-left:25px;">
          <b>{{ $datum['name'] }}</b>
        </span>
      </a>
    </div>
    <hr>
  @empty
    <p> Sorry!! No Result Found!!</p>
  @endforelse

@endsection