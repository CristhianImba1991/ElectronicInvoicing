@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript">
$.noConflict();
jQuery(document).ready(function($) {
    $('#vouchers-table').DataTable();

});
</script>
@endsection

@section('styles')
<link href="{{ asset('css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Vouchers
                </div>

                <div class="card-body">
                    <table id="vouchers-table" class="display">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>State</th>
                                <th>Voucher</th>
                                <th>Customer</th>
                                <th>Issue date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vouchers as $voucher)
                                <tr>
                                    <td>{{ $voucher->id }}</td>
                                    <td>{{ \ElectronicInvoicing\VoucherType::find($voucher->voucher_type_id)->name }}</td>
                                    <td>{{ \ElectronicInvoicing\VoucherState::find($voucher->voucher_state_id)->name }}</td>
                                    <td>{{ str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT) }}-{{ str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT) }}-{{ str_pad(strval($voucher->sequential), 9, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $voucher->customer->social_reason }}</td>
                                    <td>{{ $voucher->issue_date }}</td>
                                    <td>
                                        @if($voucher->voucher_state_id > \ElectronicInvoicing\StaticClasses\VoucherStates::SAVED)
                                            <a href="{{ route('vouchers.html', $voucher) }}" target="_blank">
                                                <img alt="HTML" height="32" width="32"  src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU2IDU2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1NiA1NjsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+CjxnPgoJPHBhdGggc3R5bGU9ImZpbGw6I0U5RTlFMDsiIGQ9Ik0zNi45ODUsMEg3Ljk2M0M3LjE1NSwwLDYuNSwwLjY1NSw2LjUsMS45MjZWNTVjMCwwLjM0NSwwLjY1NSwxLDEuNDYzLDFoNDAuMDc0ICAgYzAuODA4LDAsMS40NjMtMC42NTUsMS40NjMtMVYxMi45NzhjMC0wLjY5Ni0wLjA5My0wLjkyLTAuMjU3LTEuMDg1TDM3LjYwNywwLjI1N0MzNy40NDIsMC4wOTMsMzcuMjE4LDAsMzYuOTg1LDB6Ii8+Cgk8cG9seWdvbiBzdHlsZT0iZmlsbDojRDlEN0NBOyIgcG9pbnRzPSIzNy41LDAuMTUxIDM3LjUsMTIgNDkuMzQ5LDEyICAiLz4KCTxwYXRoIHN0eWxlPSJmaWxsOiNFQzY2MzA7IiBkPSJNNDguMDM3LDU2SDcuOTYzQzcuMTU1LDU2LDYuNSw1NS4zNDUsNi41LDU0LjUzN1YzOWg0M3YxNS41MzdDNDkuNSw1NS4zNDUsNDguODQ1LDU2LDQ4LjAzNyw1NnoiLz4KCTxnPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNMTcuNDU1LDQyLjkyNFY1M2gtMS42NDF2LTQuNTM5aC00LjM2MVY1M0g5Ljc4NVY0Mi45MjRoMS42Njh2NC40MTZoNC4zNjF2LTQuNDE2SDE3LjQ1NXoiLz4KCQk8cGF0aCBzdHlsZT0iZmlsbDojRkZGRkZGOyIgZD0iTTI3LjEwNyw0Mi45MjR2MS4xMjFIMjQuMVY1M2gtMS42NTR2LTguOTU1aC0zLjAwOHYtMS4xMjFIMjcuMTA3eiIvPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNMzYuNzA1LDQyLjkyNGgxLjY2OFY1M2gtMS42Njh2LTYuOTMybC0yLjI1Niw1LjYwNUgzM2wtMi4yNy01LjYwNVY1M2gtMS42NjhWNDIuOTI0aDEuNjY4ICAgIGwyLjk5NCw2Ljg5MUwzNi43MDUsNDIuOTI0eiIvPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNNDIuNTcsNDIuOTI0djguODMyaDQuNjM1VjUzaC02LjMwM1Y0Mi45MjRINDIuNTd6Ii8+Cgk8L2c+Cgk8Zz4KCQk8cGF0aCBzdHlsZT0iZmlsbDojRUM2NjMwOyIgZD0iTTIzLjIwNywxNi4yOTNjLTAuMzkxLTAuMzkxLTEuMDIzLTAuMzkxLTEuNDE0LDBsLTYsNmMtMC4zOTEsMC4zOTEtMC4zOTEsMS4wMjMsMCwxLjQxNGw2LDYgICAgQzIxLjk4OCwyOS45MDIsMjIuMjQ0LDMwLDIyLjUsMzBzMC41MTItMC4wOTgsMC43MDctMC4yOTNjMC4zOTEtMC4zOTEsMC4zOTEtMS4wMjMsMC0xLjQxNEwxNy45MTQsMjNsNS4yOTMtNS4yOTMgICAgQzIzLjU5OCwxNy4zMTYsMjMuNTk4LDE2LjY4NCwyMy4yMDcsMTYuMjkzeiIvPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNFQzY2MzA7IiBkPSJNNDEuMjA3LDIyLjI5M2wtNi02Yy0wLjM5MS0wLjM5MS0xLjAyMy0wLjM5MS0xLjQxNCwwcy0wLjM5MSwxLjAyMywwLDEuNDE0TDM5LjA4NiwyMyAgICBsLTUuMjkzLDUuMjkzYy0wLjM5MSwwLjM5MS0wLjM5MSwxLjAyMywwLDEuNDE0QzMzLjk4OCwyOS45MDIsMzQuMjQ0LDMwLDM0LjUsMzBzMC41MTItMC4wOTgsMC43MDctMC4yOTNsNi02ICAgIEM0MS41OTgsMjMuMzE2LDQxLjU5OCwyMi42ODQsNDEuMjA3LDIyLjI5M3oiLz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K" />
                                            </a>
                                        @endif
                                        @if($voucher->voucher_state_id >= \ElectronicInvoicing\StaticClasses\VoucherStates::SENDED)
                                            <a href="{{ route('vouchers.xml', $voucher) }}">
                                                <img alt="XML" height="32" width="32"  src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU2IDU2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1NiA1NjsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+CjxnPgoJPHBhdGggc3R5bGU9ImZpbGw6I0U5RTlFMDsiIGQ9Ik0zNi45ODUsMEg3Ljk2M0M3LjE1NSwwLDYuNSwwLjY1NSw2LjUsMS45MjZWNTVjMCwwLjM0NSwwLjY1NSwxLDEuNDYzLDFoNDAuMDc0ICAgYzAuODA4LDAsMS40NjMtMC42NTUsMS40NjMtMVYxMi45NzhjMC0wLjY5Ni0wLjA5My0wLjkyLTAuMjU3LTEuMDg1TDM3LjYwNywwLjI1N0MzNy40NDIsMC4wOTMsMzcuMjE4LDAsMzYuOTg1LDB6Ii8+Cgk8cG9seWdvbiBzdHlsZT0iZmlsbDojRDlEN0NBOyIgcG9pbnRzPSIzNy41LDAuMTUxIDM3LjUsMTIgNDkuMzQ5LDEyICAiLz4KCTxwYXRoIHN0eWxlPSJmaWxsOiNGMjlDMUY7IiBkPSJNNDguMDM3LDU2SDcuOTYzQzcuMTU1LDU2LDYuNSw1NS4zNDUsNi41LDU0LjUzN1YzOWg0M3YxNS41MzdDNDkuNSw1NS4zNDUsNDguODQ1LDU2LDQ4LjAzNyw1NnoiLz4KCTxnPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNMTkuMzc5LDQ4LjEwNUwyMS45MzYsNTNoLTEuOWwtMS42LTMuODAxaC0wLjEzN0wxNi41NzYsNTNoLTEuOWwyLjU1Ny00Ljg5NWwtMi43MjEtNS4xODJoMS44NzMgICAgbDEuNzc3LDQuMTAyaDAuMTM3bDEuOTI4LTQuMTAySDIyLjFMMTkuMzc5LDQ4LjEwNXoiLz4KCQk8cGF0aCBzdHlsZT0iZmlsbDojRkZGRkZGOyIgZD0iTTMxLjk5OCw0Mi45MjRoMS42NjhWNTNoLTEuNjY4di02LjkzMmwtMi4yNTYsNS42MDVoLTEuNDQ5bC0yLjI3LTUuNjA1VjUzaC0xLjY2OFY0Mi45MjRoMS42NjggICAgbDIuOTk0LDYuODkxTDMxLjk5OCw0Mi45MjR6Ii8+CgkJPHBhdGggc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIGQ9Ik0zNy44NjMsNDIuOTI0djguODMyaDQuNjM1VjUzaC02LjMwM1Y0Mi45MjRIMzcuODYzeiIvPgoJPC9nPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0xNS41LDI0Yy0wLjI1NiwwLTAuNTEyLTAuMDk4LTAuNzA3LTAuMjkzYy0wLjM5MS0wLjM5MS0wLjM5MS0xLjAyMywwLTEuNDE0bDYtNiAgIGMwLjM5MS0wLjM5MSwxLjAyMy0wLjM5MSwxLjQxNCwwczAuMzkxLDEuMDIzLDAsMS40MTRsLTYsNkMxNi4wMTIsMjMuOTAyLDE1Ljc1NiwyNCwxNS41LDI0eiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0yMS41LDMwYy0wLjI1NiwwLTAuNTEyLTAuMDk4LTAuNzA3LTAuMjkzbC02LTZjLTAuMzkxLTAuMzkxLTAuMzkxLTEuMDIzLDAtMS40MTQgICBzMS4wMjMtMC4zOTEsMS40MTQsMGw2LDZjMC4zOTEsMC4zOTEsMC4zOTEsMS4wMjMsMCwxLjQxNEMyMi4wMTIsMjkuOTAyLDIxLjc1NiwzMCwyMS41LDMweiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0zMy41LDMwYy0wLjI1NiwwLTAuNTEyLTAuMDk4LTAuNzA3LTAuMjkzYy0wLjM5MS0wLjM5MS0wLjM5MS0xLjAyMywwLTEuNDE0bDYtNiAgIGMwLjM5MS0wLjM5MSwxLjAyMy0wLjM5MSwxLjQxNCwwczAuMzkxLDEuMDIzLDAsMS40MTRsLTYsNkMzNC4wMTIsMjkuOTAyLDMzLjc1NiwzMCwzMy41LDMweiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0zOS41LDI0Yy0wLjI1NiwwLTAuNTEyLTAuMDk4LTAuNzA3LTAuMjkzbC02LTZjLTAuMzkxLTAuMzkxLTAuMzkxLTEuMDIzLDAtMS40MTQgICBzMS4wMjMtMC4zOTEsMS40MTQsMGw2LDZjMC4zOTEsMC4zOTEsMC4zOTEsMS4wMjMsMCwxLjQxNEM0MC4wMTIsMjMuOTAyLDM5Ljc1NiwyNCwzOS41LDI0eiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0yNC41LDMyYy0wLjExLDAtMC4yMjMtMC4wMTktMC4zMzMtMC4wNThjLTAuNTIxLTAuMTg0LTAuNzk0LTAuNzU1LTAuNjEtMS4yNzZsNi0xNyAgIGMwLjE4NS0wLjUyMSwwLjc1My0wLjc5NSwxLjI3Ni0wLjYxYzAuNTIxLDAuMTg0LDAuNzk0LDAuNzU1LDAuNjEsMS4yNzZsLTYsMTdDMjUuMjk4LDMxLjc0NCwyNC45MTIsMzIsMjQuNSwzMnoiLz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K" />
                                            </a>
                                        @endif
                                        @if($voucher->voucher_state_id >= \ElectronicInvoicing\StaticClasses\VoucherStates::SENDED)
                                            <a href="{{ route('vouchers.pdf', $voucher) }}">
                                                <img alt="PDF" height="32" width="32"  src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU2IDU2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1NiA1NjsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+CjxnPgoJPHBhdGggc3R5bGU9ImZpbGw6I0U5RTlFMDsiIGQ9Ik0zNi45ODUsMEg3Ljk2M0M3LjE1NSwwLDYuNSwwLjY1NSw2LjUsMS45MjZWNTVjMCwwLjM0NSwwLjY1NSwxLDEuNDYzLDFoNDAuMDc0ICAgYzAuODA4LDAsMS40NjMtMC42NTUsMS40NjMtMVYxMi45NzhjMC0wLjY5Ni0wLjA5My0wLjkyLTAuMjU3LTEuMDg1TDM3LjYwNywwLjI1N0MzNy40NDIsMC4wOTMsMzcuMjE4LDAsMzYuOTg1LDB6Ii8+Cgk8cG9seWdvbiBzdHlsZT0iZmlsbDojRDlEN0NBOyIgcG9pbnRzPSIzNy41LDAuMTUxIDM3LjUsMTIgNDkuMzQ5LDEyICAiLz4KCTxwYXRoIHN0eWxlPSJmaWxsOiNDQzRCNEM7IiBkPSJNMTkuNTE0LDMzLjMyNEwxOS41MTQsMzMuMzI0Yy0wLjM0OCwwLTAuNjgyLTAuMTEzLTAuOTY3LTAuMzI2ICAgYy0xLjA0MS0wLjc4MS0xLjE4MS0xLjY1LTEuMTE1LTIuMjQyYzAuMTgyLTEuNjI4LDIuMTk1LTMuMzMyLDUuOTg1LTUuMDY4YzEuNTA0LTMuMjk2LDIuOTM1LTcuMzU3LDMuNzg4LTEwLjc1ICAgYy0wLjk5OC0yLjE3Mi0xLjk2OC00Ljk5LTEuMjYxLTYuNjQzYzAuMjQ4LTAuNTc5LDAuNTU3LTEuMDIzLDEuMTM0LTEuMjE1YzAuMjI4LTAuMDc2LDAuODA0LTAuMTcyLDEuMDE2LTAuMTcyICAgYzAuNTA0LDAsMC45NDcsMC42NDksMS4yNjEsMS4wNDljMC4yOTUsMC4zNzYsMC45NjQsMS4xNzMtMC4zNzMsNi44MDJjMS4zNDgsMi43ODQsMy4yNTgsNS42Miw1LjA4OCw3LjU2MiAgIGMxLjMxMS0wLjIzNywyLjQzOS0wLjM1OCwzLjM1OC0wLjM1OGMxLjU2NiwwLDIuNTE1LDAuMzY1LDIuOTAyLDEuMTE3YzAuMzIsMC42MjIsMC4xODksMS4zNDktMC4zOSwyLjE2ICAgYy0wLjU1NywwLjc3OS0xLjMyNSwxLjE5MS0yLjIyLDEuMTkxYy0xLjIxNiwwLTIuNjMyLTAuNzY4LTQuMjExLTIuMjg1Yy0yLjgzNywwLjU5My02LjE1LDEuNjUxLTguODI4LDIuODIyICAgYy0wLjgzNiwxLjc3NC0xLjYzNywzLjIwMy0yLjM4Myw0LjI1MUMyMS4yNzMsMzIuNjU0LDIwLjM4OSwzMy4zMjQsMTkuNTE0LDMzLjMyNHogTTIyLjE3NiwyOC4xOTggICBjLTIuMTM3LDEuMjAxLTMuMDA4LDIuMTg4LTMuMDcxLDIuNzQ0Yy0wLjAxLDAuMDkyLTAuMDM3LDAuMzM0LDAuNDMxLDAuNjkyQzE5LjY4NSwzMS41ODcsMjAuNTU1LDMxLjE5LDIyLjE3NiwyOC4xOTh6ICAgIE0zNS44MTMsMjMuNzU2YzAuODE1LDAuNjI3LDEuMDE0LDAuOTQ0LDEuNTQ3LDAuOTQ0YzAuMjM0LDAsMC45MDEtMC4wMSwxLjIxLTAuNDQxYzAuMTQ5LTAuMjA5LDAuMjA3LTAuMzQzLDAuMjMtMC40MTUgICBjLTAuMTIzLTAuMDY1LTAuMjg2LTAuMTk3LTEuMTc1LTAuMTk3QzM3LjEyLDIzLjY0OCwzNi40ODUsMjMuNjcsMzUuODEzLDIzLjc1NnogTTI4LjM0MywxNy4xNzQgICBjLTAuNzE1LDIuNDc0LTEuNjU5LDUuMTQ1LTIuNjc0LDcuNTY0YzIuMDktMC44MTEsNC4zNjItMS41MTksNi40OTYtMi4wMkMzMC44MTUsMjEuMTUsMjkuNDY2LDE5LjE5MiwyOC4zNDMsMTcuMTc0eiAgICBNMjcuNzM2LDguNzEyYy0wLjA5OCwwLjAzMy0xLjMzLDEuNzU3LDAuMDk2LDMuMjE2QzI4Ljc4MSw5LjgxMywyNy43NzksOC42OTgsMjcuNzM2LDguNzEyeiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0NDNEI0QzsiIGQ9Ik00OC4wMzcsNTZINy45NjNDNy4xNTUsNTYsNi41LDU1LjM0NSw2LjUsNTQuNTM3VjM5aDQzdjE1LjUzN0M0OS41LDU1LjM0NSw0OC44NDUsNTYsNDguMDM3LDU2eiIvPgoJPGc+CgkJPHBhdGggc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIGQ9Ik0xNy4zODUsNTNoLTEuNjQxVjQyLjkyNGgyLjg5OGMwLjQyOCwwLDAuODUyLDAuMDY4LDEuMjcxLDAuMjA1ICAgIGMwLjQxOSwwLjEzNywwLjc5NSwwLjM0MiwxLjEyOCwwLjYxNWMwLjMzMywwLjI3MywwLjYwMiwwLjYwNCwwLjgwNywwLjk5MXMwLjMwOCwwLjgyMiwwLjMwOCwxLjMwNiAgICBjMCwwLjUxMS0wLjA4NywwLjk3My0wLjI2LDEuMzg4Yy0wLjE3MywwLjQxNS0wLjQxNSwwLjc2NC0wLjcyNSwxLjA0NmMtMC4zMSwwLjI4Mi0wLjY4NCwwLjUwMS0xLjEyMSwwLjY1NiAgICBzLTAuOTIxLDAuMjMyLTEuNDQ5LDAuMjMyaC0xLjIxN1Y1M3ogTTE3LjM4NSw0NC4xNjh2My45OTJoMS41MDRjMC4yLDAsMC4zOTgtMC4wMzQsMC41OTUtMC4xMDMgICAgYzAuMTk2LTAuMDY4LDAuMzc2LTAuMTgsMC41NC0wLjMzNWMwLjE2NC0wLjE1NSwwLjI5Ni0wLjM3MSwwLjM5Ni0wLjY0OWMwLjEtMC4yNzgsMC4xNS0wLjYyMiwwLjE1LTEuMDMyICAgIGMwLTAuMTY0LTAuMDIzLTAuMzU0LTAuMDY4LTAuNTY3Yy0wLjA0Ni0wLjIxNC0wLjEzOS0wLjQxOS0wLjI4LTAuNjE1Yy0wLjE0Mi0wLjE5Ni0wLjM0LTAuMzYtMC41OTUtMC40OTIgICAgYy0wLjI1NS0wLjEzMi0wLjU5My0wLjE5OC0xLjAxMi0wLjE5OEgxNy4zODV6Ii8+CgkJPHBhdGggc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIGQ9Ik0zMi4yMTksNDcuNjgyYzAsMC44MjktMC4wODksMS41MzgtMC4yNjcsMi4xMjZzLTAuNDAzLDEuMDgtMC42NzcsMS40NzdzLTAuNTgxLDAuNzA5LTAuOTIzLDAuOTM3ICAgIHMtMC42NzIsMC4zOTgtMC45OTEsMC41MTNjLTAuMzE5LDAuMTE0LTAuNjExLDAuMTg3LTAuODc1LDAuMjE5QzI4LjIyMiw1Mi45ODQsMjguMDI2LDUzLDI3Ljg5OCw1M2gtMy44MTRWNDIuOTI0aDMuMDM1ICAgIGMwLjg0OCwwLDEuNTkzLDAuMTM1LDIuMjM1LDAuNDAzczEuMTc2LDAuNjI3LDEuNiwxLjA3M3MwLjc0LDAuOTU1LDAuOTUsMS41MjRDMzIuMTE0LDQ2LjQ5NCwzMi4yMTksNDcuMDgsMzIuMjE5LDQ3LjY4MnogICAgIE0yNy4zNTIsNTEuNzk3YzEuMTEyLDAsMS45MTQtMC4zNTUsMi40MDYtMS4wNjZzMC43MzgtMS43NDEsMC43MzgtMy4wOWMwLTAuNDE5LTAuMDUtMC44MzQtMC4xNS0xLjI0NCAgICBjLTAuMTAxLTAuNDEtMC4yOTQtMC43ODEtMC41ODEtMS4xMTRzLTAuNjc3LTAuNjAyLTEuMTY5LTAuODA3cy0xLjEzLTAuMzA4LTEuOTE0LTAuMzA4aC0wLjk1N3Y3LjYyOUgyNy4zNTJ6Ii8+CgkJPHBhdGggc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIGQ9Ik0zNi4yNjYsNDQuMTY4djMuMTcyaDQuMjExdjEuMTIxaC00LjIxMVY1M2gtMS42NjhWNDIuOTI0SDQwLjl2MS4yNDRIMzYuMjY2eiIvPgoJPC9nPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+Cjwvc3ZnPgo=" />
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
