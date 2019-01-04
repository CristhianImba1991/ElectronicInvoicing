<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <!--<script src="{{ asset('js/app.js') }}" defer></script>-->
    <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    @yield('scripts')

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <!--<link href="{{ asset('css/app.css') }}" rel="stylesheet">-->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    @yield('styles')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        @auth
                            @if(auth()->user()->can('read_companies') || auth()->user()->can('read_branches') || auth()->user()->can('read_emission_points') || auth()->user()->can('read_customers') || auth()->user()->can('read_users') || auth()->user()->can('read_products'))
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        Manage <span class="caret"></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
                                        @can('read_companies')
                                            <a href="{{ route('companies.index') }}" class="dropdown-item">Companies</a>
                                        @endcan
                                        @can('read_branches')
                                            <a href="{{ route('branches.index') }}" class="dropdown-item">Branches</a>
                                        @endcan
                                        @can('read_emission_points')
                                            <a href="{{ route('emission_points.index') }}" class="dropdown-item">Emission points</a>
                                        @endcan
                                        @can('read_users')
                                            <a href="{{ route('users.index') }}" class="dropdown-item">Users</a>
                                        @endcan
                                        @can('read_customers')
                                            <a href="{{ route('customers.index') }}" class="dropdown-item">Customers</a>
                                        @endcan
                                        @can('read_products')
                                            <a href="{{ route('products.index') }}" class="dropdown-item">Products</a>
                                        @endcan
                                    </div>
                                </li>
                            @endif
                            @if(auth()->user()->can('create_vouchers') || auth()->user()->can('read_vouchers'))
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        Vouchers <span class="caret"></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
                                        @can('create_vouchers')
                                            <a href="{{ route('vouchers.create') }}" class="dropdown-item">New voucher</a>
                                            <a href="{{ route('vouchers.index_draft') }}" class="dropdown-item">Draft vouchers</a>
                                        @endcan
                                        @can('read_vouchers')
                                            <a href="{{ route('vouchers.index') }}" class="dropdown-item">Reports</a>
                                        @endcan
                                    </div>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
