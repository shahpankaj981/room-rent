@extends('layouts.app')

@section('content')
  <div class="container">

    {!! Form::open(['route'=>['room.updateProfileImage', $userId] ,  'files'=>true])!!}
    <div class="header">
      <h2>Update Profile Image</h2>
    </div>
    <hr>
    {!! Form::file('profileImage[]') !!}
    <br>
    {!! Form::submit('Upload') !!}
    {!! Form::close() !!}
  </div>
@endsection

