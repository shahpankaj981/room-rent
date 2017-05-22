@extends('layouts.app')
@section('content')

  {!! Form::open(['route'=>'forgotPasswordCheckStatus']) !!}
  <div class="form-header">
    <h3>Password Recovery Form</h3>
  </div>
  <hr>
  <div class="form-group">
      {!! Form::text('identity', 'Enter Username / Email', ['class' => 'form-control']) !!}
    <br>
    {!! Form::submit('Submit', ['class' => 'form-control']) !!}
  </div>
  {!! Form::close() !!}

  @endsection