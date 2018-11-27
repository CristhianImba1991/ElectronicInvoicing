<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title></title>
        <script src="{{ $html ? asset('js/jquery-3.3.1.min.js') : public_path('js/jquery-3.3.1.min.js') }}"></script>
        <script src="{{ $html ? asset('js/bootstrap.min.js') : public_path('js/bootstrap.min.js') }}"></script>
        <link rel="stylesheet" href="{{ $html ? asset('css/bootstrap.min.css') : public_path('css/bootstrap.min.css') }}">
        <style>
            body {
                font-size: 10pt;
            }
        </style>
    </head>
    <body>
        <table class="table table-borderless">
            <tbody>
                <tr>
                    <td class="align-bottom">@include('vouchers.ride.company')</td>
                    <td class="align-bottom">@include('vouchers.ride.information')</td>
                </tr>
                <tr>
                    <td colspan="2">@yield('body')</td>
                </tr>
                <tr>
                    <td colspan="2">@yield('footer')</td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
