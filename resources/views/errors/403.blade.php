@extends('layouts.error')

@section('title')
<title>{{ __('view.page_not_found') }}</title>
@endsection

@section('error_code')
<div class="text-black text-5xl md:text-15xl font-black">
    404
</div>
@endsection

@section('message')
<p class="text-grey-darker text-2xl md:text-3xl font-light mb-8 leading-normal">
    {{ __('view.sorry_the_page_you_are_looking_for_could_not_be_found_or_you_do_not_have') }}
</p>
@endsection

@section('action')
<a href="{{ redirect()->getUrlGenerator()->previous() }}">
    <button class="bg-transparent text-grey-darkest font-bold uppercase tracking-wide py-3 px-6 border-2 border-grey-light hover:border-grey rounded-lg">
        {{ __('view.go_back') }}
    </button>
</a>
@endsection

@section('image')
<div class="relative pb-full md:flex md:pb-0 md:min-h-screen w-full md:w-1/2">
    <div style="background-image: url('/svg/404.svg');" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center"></div>
</div>
@endsection
