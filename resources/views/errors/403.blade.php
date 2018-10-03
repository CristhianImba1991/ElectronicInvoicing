@extends('layouts.error')

@section('title')
<title>Page Not Found</title>
@endsection

@section('error_code')
<div class="text-black text-5xl md:text-15xl font-black">
    404
</div>
@endsection

@section('message')
<p class="text-grey-darker text-2xl md:text-3xl font-light mb-8 leading-normal">
    Sorry, the page you are looking for could not be found or you do not have the necessary permissions to access the resource.
</p>
@endsection

@section('image')
<div class="relative pb-full md:flex md:pb-0 md:min-h-screen w-full md:w-1/2">
    <div style="background-image: url('/svg/404.svg');" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center"></div>
</div>
@endsection
