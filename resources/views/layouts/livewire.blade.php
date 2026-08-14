@extends('adminlte::page')

@section('content_header')
<div class="d-flex justify-content-between">
    {{ $header ?? '' }}
</div>
@endsection

@section('content')
    {{ $slot }}
@endsection
