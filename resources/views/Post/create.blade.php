@extends('layouts.app')

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
          <div class="panel-heading">Create new post</div>
          <div class="panel-body">
            {!! Form::open(['route'=>'room.store', 'class'=>'form-horizontal' ,'files' => true]) !!}

            <div class="form-group">
              {!! Form::label('title', 'Title', ['class' => 'control-label']) !!}
              {!! Form::text('title', 'Title', ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
              {!! Form::label('location', 'Location', ['class' => 'control-label']) !!}
              {!! Form::text('location', 'Location', ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
              {!! Form::label('numberOfRooms', 'Number Of Rooms', ['class' => 'control-label']) !!}
              {!! Form::selectRange('numberOfRooms', 1, 99 , null , ['placeholder'=>'Pick the number of rooms', 'class' => 'form-control']) !!}
            </div>
            <div class="form-group">
              {!! Form::label('price', 'Price', ['class' => 'control-label']) !!}
              {!! Form::text('price', 'Price', ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
              {!! Form::label('postType', 'Type Of Post', ['class' => 'control-label']) !!}
              <label>
              {!! Form::radio('postType', '1', null,  ['id' => 'postType']) !!}
              Ask
              </label>
              <label>
              {!! Form::radio('postType', '2', null,  ['id' => 'postType']) !!}
              Offer
              </label>
            </div>

            <div class="form-group">
              {!! Form::label('description', 'Description', ['class' => 'control-label']) !!}
              {!! Form::textarea('description', 'Description', ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
              {!! Form::label('images', 'Images', ['class' => 'control-label']) !!}
              {!! Form::file('images[]', ['multiple'=> true]) !!}
            </div>



            </div>
            <hr>
            <div class="form-group">
             {!! Form::submit('Create', ['class' => 'form-control']) !!}
            </div>

            {!! Form::close() !!}
            </div>
          </div>
        </div>
      </div>

@endsection

