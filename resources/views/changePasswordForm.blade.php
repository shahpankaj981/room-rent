@extends('layouts.app')

@section('content')
  {!! Form::open(['route'=>'room.changePassword', 'class'=>'form-horizontal']) !!}
  <div class="form-header">
    <h2>Change Password</h2>
  </div>
  <div class="col-md-4">
    {!! Form::label('oldPassword', 'Old Password', ['class' => 'control-label']) !!}
  </div>
  <div class="col-md-6">
    {!! Form::password('oldPassword', ['required', 'autofocus', 'class' => 'control-label']) !!}
  </div>
  <br>
  <div class="col-md-4">
    {!! Form::label('newPassword', 'New Password', ['class' => 'control-label']) !!}
  </div>
  <div class="col-md-6">
    {!! Form::password('newPassword', ['required', 'autofocus', 'class' => 'control-label']) !!}
  </div>
  <br>
  <div class="col-md-4">
    {!! Form::label('confirmPassword', 'Confirm Password', ['class' => 'control-label']) !!}
  </div>
  <div class="col-md-6">
    {!! Form::password('confirmPassword', ['required', 'autofocus', 'class' => 'control-label']) !!}
  </div>
  <hr>
  <br>

  <div class="col-md-3">
  {!! Form::submit('Submit', ['class' => 'form-control']) !!}
  </div>

  {!! Form::close() !!}
@endsection