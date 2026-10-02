@extends('layouts.admin')

@section('title', 'File')

@section('content')
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">
        Halo, {{ auth()->user()->full_name }}! 👋
    </h1>
@endsection