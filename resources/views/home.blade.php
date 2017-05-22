@extends('layouts.app')

@section('content')
  <style>
    span.home-area {
      width: 500px;
    }
    a:hover{
      color:red !important;
    }

  </style>
  <div class="container">
    @if(Session::has('flash_message'))
      <div class="alert alert-success">{{ Session::get('flash_message') }}</div>
    @endif
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">

          <div class="panel-body">
            <h2> Welcome to Room rent.</h2>
            <br/>


            <a href="{{route('room.profile', ['userId'=>$user->id])}}">
                        <span class="col-md-5"
                              style="background-color: #dedede; border-radius: 4px; margin:2px; padding-top:15px; padding-bottom: 15px; text-align:center;">
                        <b
                            style="font-size: 25px; color:black;"> View Profile</b>
                        </span>
            </a>

            <a href="{{route('message.viewThreads')}}">
                      <span class="col-md-5"
                            style="background-color: #999; border-radius: 4px;margin:2px; padding-top:15px; padding-bottom: 15px; text-align:center;">
                        <b
                            style="font-size: 25px;color:black;">Messages</b>
                      </span>
            </a>

            <a href="{{route('room.showallposts', ['postType'=>'1'])}}">
                        <span class="col-md-5"
                              style="background-color: #999; border-radius: 4px;margin:2px; padding-top:15px; padding-bottom: 15px; text-align:center;">
                          <b
                              style="font-size: 25px;color:black;">View Ask Posts</b>
                        </span>
            </a>


            <a href="{{route('room.showallposts', ['postType'=>'2'])}}">
                        <span class="col-md-5"
                              style="background-color: #dedede; border-radius: 4px;margin:2px; padding-top:15px; padding-bottom: 15px; text-align:center;">
                          <b
                              style="font-size: 25px;color:black;">View Offer Posts</b>
                        </span>
            </a>

            <a href="{{ route('room.create') }}"><b style="font-size: 25px;color:black;">
                           <span class="col-md-10"
                                 style="background-color: #898; border-radius: 4px;margin:2px; padding-top:15px; padding-bottom: 15px; text-align:center;">
                              <b style="font-size: 25px;color:black;">Create new post for room</b>
                           </span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <br>
  <br>
  <footer style=" height: 45px; width: 100%; background-color: #565656; text-align: center">
    <p style="color: #fff; line-height: 40px;font-size: 0.7em;">&copy; Copyright 2017, RoomRent</p>
  </footer>
@endsection