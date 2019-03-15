@extends('layouts.error')

@section('title')
<title>{{ __('view.internal_server_error') }}</title>
@endsection

@section('error_code')
<div class="text-black text-5xl md:text-15xl font-black">
    500
</div>
@endsection

@section('message')
<p class="text-grey-darker text-2xl md:text-3xl font-light mb-8 leading-normal">
    {{ __('view.sorry_we_are_experiencing_technical_difficulties_with_our_server_right_now') }}
</p>
@endsection

@section('action')
<a href="{{ route('login') }}">
    <button class="bg-transparent text-grey-darkest font-bold uppercase tracking-wide py-3 px-6 border-2 border-grey-light hover:border-grey rounded-lg">
        {{ __('view.go_home') }}
    </button>
</a>
@endsection

@section('image')
<div class="relative pb-full md:flex md:pb-0 md:min-h-screen w-full md:w-1/2">
    <div style="background-image: url('/svg/500.svg');" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center"></div>
</div>
@endsection
