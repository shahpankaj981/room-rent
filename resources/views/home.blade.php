@extends('layouts.app')

@section('content')
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
                    <div>
                      <a href="{{route('room.profile', ['userId'=>$user->id])}}" class ="btn btn-default"> View Profile</a>
                    </div>
                    <div>
                        <a href="{{route('room.showallposts', ['postType'=>'1'])}}" class="btn btn-default">View Ask Posts</a>
                    </div>

                    <div>
                        <a href="{{route('room.showallposts', ['postType'=>'2'])}}" class="btn btn-default">View Offer Posts</a>
                    </div>

                    <div>
                        <a href="{{ route('room.create') }}" class="btn btn-default">Create new post for room.</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
