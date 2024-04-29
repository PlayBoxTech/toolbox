@extends('layout2')

@section('header')
    Results for {{ $domain }}
@endsection

@section('body')
            {{ $error }}
            Domain may also be on ClientHold! 
@endsection
