@extends('layouts.app')

@section('content')

  <div class='container'>

    {{ Form::model($user, ['route' => ['room.updateProfileInfo', $user->id], 'class'=>'form-horizontal']) }}

      <!-- name -->
      <div class="form-group">
        {!! Form::label('name', 'Name', ['class' => 'col-md-4 control-label']) !!}
        {{ Form::text('name') }}
      </div>

      <!--userName-->
      <div class="form-group">
        {!! Form::label('userName', 'User Name', ['class' => 'col-md-4 control-label']) !!}
        {{ Form::text('userName') }}
      </div>

      <!--phone-->
      <div class="form-group">
        {!! Form::label('phone', 'Phone', ['class' => 'col-md-4 control-label']) !!}
        {{  Form::text('phone') }}
      </div>

      <!--submit button -->
      <div class="form-group">
        {{ Form::submit('Update', ['class' => 'form-control']) }}
      </div>

    {{ Form::close() }}

  </div>

@endsection

{{--<div class="form-group">--}}
  {{--{!! Form::label('name', 'Name', ['class' => 'col-md-4 control-label']) !!}--}}
  {{--<div class="col-md-6">--}}
    {{--{!! Form::text('name', 'Name', ['required', 'autofocus', 'class' => 'form-control']) !!}--}}
  {{--</div>--}}
{{--</div>--}}

{{--<div class="form-group">--}}
  {{--{!! Form::label('userName', 'User Name', ['class' => 'col-md-4 control-label']) !!}--}}
  {{--<div class="col-md-6">--}}
    {{--{!! Form::text('userName', 'User Name', ['required', 'autofocus', 'class' => 'form-control']) !!}--}}
  {{--</div>--}}
{{--</div>--}}

{{--<div class="form-group">--}}
  {{--{!! Form::label('phone', 'Phone', ['class' => 'col-md-4 control-label']) !!}--}}
  {{--<div class="col-md-6">--}}
    {{--{!! Form::text('phone', 'Phone', ['required', 'autofocus', 'class' => 'form-control']) !!}--}}
  {{--</div>--}}
{{--</div>--}}

{{--<div class="form-group">--}}
  {{--<div class="col-md-6">--}}
    {{--{!! Form::submit('Done', ['class' => 'form-control']) !!}--}}
  {{--</div>--}}
{{--</div>--}}