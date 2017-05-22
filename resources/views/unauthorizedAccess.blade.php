@extends('layouts.app')

@section('content')

  @if($response)
    <h1>{{$response}}</h1>
  @else
    <h1>Unauthorized Access</h1>

    @component('alert', ['message' => 'Whoops!!'])
      You are not authorized to access this!!
    @endcomponent
  @endif



@endsection