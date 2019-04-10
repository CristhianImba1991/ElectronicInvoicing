@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-tokenfield.min.js') }}"></script>
<script type="text/javascript">
$.noConflict();
jQuery(document).ready(function($) {
    $('#company').change(function() {
        if($(this).val() != '') {
            var _token = $('input[name = "_token"]').val();
            var id = $(this).val();
            $.ajax({
                url: "{{ route('companies.branches') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var branches = JSON.parse(result);
                    var options = '';
                    for (var i = 0; i < branches.length; i++) {
                        options += '<option value="' + branches[i]['id'] + '">' + branches[i]['company']['tradename'] + ': ' + branches[i]['name'] + '</option>';
                    }
                    $("#branch").html(options).selectpicker('refresh');
                    $("#emission_point").html('').selectpicker('refresh');
                }
            })
        }
    });
    $('#branch').change(function() {
        if($(this).val() != '') {
            var _token = $('input[name = "_token"]').val();
            var id = $(this).val();
            $.ajax({
                url: "{{ route('branches.emissionPoints') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var emissionPoints = JSON.parse(result);
                    var options = '';
                    for (var i = 0; i < emissionPoints.length; i++) {
                        options += '<option value="' + emissionPoints[i]['id'] + '">' + emissionPoints[i]['branch']['company']['tradename'] + ' (' + emissionPoints[i]['branch']['name'] + '): ' + emissionPoints[i]['code'] + '</option>';
                    }
                    $("#emission_point").html(options).selectpicker('refresh');
                }
            })
        }
    });
    $('#vouchers-table').DataTable({
        "order": [[ 5, 'desc' ], [ 0, 'desc' ]]
    });
    @unlessrole('customer')
        $('#emailModal').on('show.bs.modal', function(event) {
            $(this).find("#voucher").val($(event.relatedTarget).data('voucher'))
        });
        $('#emailModal input[id = email]')
            .on('tokenfield:createtoken', function (e) {
                var data = e.attrs.value.split('|')
                e.attrs.value = data[1] || data[0]
                e.attrs.label = data[1] ? data[0] + ' (' + data[1] + ')' : data[0]
            })
            .on('tokenfield:createdtoken', function (e) {
                var re = /\S+@\S+\.\S+/
                var valid = re.test(e.attrs.value)
                if (!valid) {
                    $(e.relatedTarget).addClass('invalid')
                }
            })
            .on('tokenfield:edittoken', function (e) {
                if (e.attrs.label !== e.attrs.value) {
                    var label = e.attrs.label.split(' (')
                    e.attrs.value = label[0] + '|' + e.attrs.value
                }
            })
            .tokenfield({
                beautify: false,
                createTokensOnBlur: true,
                inputType: 'email',
            });
        $("#submit_email").click(function() {
            $.ajax({
                url: "{{ route('vouchers.sendmail') }}",
                method: "POST",
                data: $('#modal-form').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        $('#emailModal').modal('toggle');
                        $('#modal-form').trigger('reset');
                        $('#emailModal input[id = email]').tokenfield('setTokens', []);
                    } else {
                        $('#validation').on('show.bs.modal', function(event) {
                            var errors = '';
                            $.each(validator['messages'], function(field, message) {
                                errors += "<li>" + message + "</li>";
                            });
                            $(this).find('#modal-body').html("<ul>" + errors + "</ul>");
                        });
                        $('#validation').modal('show');
                    }
                }
            });
        });
    @endunlessrole
});
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css') }}">
<link href="{{ asset('css/jquery.dataTables.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/bootstrap-tokenfield.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/tokenfield-typeahead.min.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ ucfirst(trans_choice(__('view.voucher'), 1)) }}
                </div>

                <div class="card-body">
                    <table id="vouchers-table" class="display">
                        <thead>
                            <tr>
                                <th>{{ __('view.id') }}</th>
                                <th>{{ ucfirst(trans_choice(__('view.company'), 0)) }}</th>
                                <th>{{ __('view.type') }}</th>
                                <th>{{ __('view.state') }}</th>
                                <th>{{ ucfirst(trans_choice(__('view.voucher'), 0)) }}</th>
                                <th>{{ ucfirst(trans_choice(__('view.customer'), 0)) }}</th>
                                <th>{{ __('view.issue_date') }}</th>
                                <th>{{ __('view.view') }}</th>
                                @unlessrole('customer')
                                    <th></th>
                                @endunlessrole
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vouchers as $voucher)
                                <tr>
                                    <td>{{ $voucher->id }}</td>
                                    <td>{{ $voucher->emissionPoint->branch->company->tradename }}</td>
                                    <td>{{ \ElectronicInvoicing\VoucherType::find($voucher->voucher_type_id)->name }}</td>
                                    <td>
                                        @switch($voucher->voucher_state_id)
                                            @case(3)
                                                <span class="badge badge-info">
                                                @break
                                            @case(4)
                                                <span class="badge badge-dark">
                                                @break
                                            @case(5)
                                                <span class="badge badge-light">
                                                @break
                                            @case(6)
                                                <span class="badge badge-secondary">
                                                @break
                                            @case(7)
                                                <span class="badge badge-warning">
                                                @break
                                            @case(8)
                                                <span class="badge badge-success">
                                                @break
                                            @case(9)
                                                <span class="badge badge-primary">
                                                @break
                                            @case(10)
                                                <span class="badge badge-danger">
                                                @break
                                            @default
                                                <span class="badge">
                                        @endswitch
                                        {{ __(\ElectronicInvoicing\VoucherState::find($voucher->voucher_state_id)->name) }}</span>
                                    </td>
                                    <td>{{ str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT) }}-{{ str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT) }}-{{ str_pad(strval($voucher->sequential), 9, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ \ElectronicInvoicing\Customer::withTrashed()->find($voucher->customer_id)->social_reason }}</td>
                                    <td>{{ $voucher->issue_date }}</td>
                                    <td>
                                        @if($voucher->voucher_state_id >= \ElectronicInvoicing\StaticClasses\VoucherStates::SAVED)
                                            <a href="{{ route('vouchers.html', $voucher) }}" target="_blank">
                                                <img alt="HTML" height="32" width="32"  src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU2IDU2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1NiA1NjsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+CjxnPgoJPHBhdGggc3R5bGU9ImZpbGw6I0U5RTlFMDsiIGQ9Ik0zNi45ODUsMEg3Ljk2M0M3LjE1NSwwLDYuNSwwLjY1NSw2LjUsMS45MjZWNTVjMCwwLjM0NSwwLjY1NSwxLDEuNDYzLDFoNDAuMDc0ICAgYzAuODA4LDAsMS40NjMtMC42NTUsMS40NjMtMVYxMi45NzhjMC0wLjY5Ni0wLjA5My0wLjkyLTAuMjU3LTEuMDg1TDM3LjYwNywwLjI1N0MzNy40NDIsMC4wOTMsMzcuMjE4LDAsMzYuOTg1LDB6Ii8+Cgk8cG9seWdvbiBzdHlsZT0iZmlsbDojRDlEN0NBOyIgcG9pbnRzPSIzNy41LDAuMTUxIDM3LjUsMTIgNDkuMzQ5LDEyICAiLz4KCTxwYXRoIHN0eWxlPSJmaWxsOiNFQzY2MzA7IiBkPSJNNDguMDM3LDU2SDcuOTYzQzcuMTU1LDU2LDYuNSw1NS4zNDUsNi41LDU0LjUzN1YzOWg0M3YxNS41MzdDNDkuNSw1NS4zNDUsNDguODQ1LDU2LDQ4LjAzNyw1NnoiLz4KCTxnPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNMTcuNDU1LDQyLjkyNFY1M2gtMS42NDF2LTQuNTM5aC00LjM2MVY1M0g5Ljc4NVY0Mi45MjRoMS42Njh2NC40MTZoNC4zNjF2LTQuNDE2SDE3LjQ1NXoiLz4KCQk8cGF0aCBzdHlsZT0iZmlsbDojRkZGRkZGOyIgZD0iTTI3LjEwNyw0Mi45MjR2MS4xMjFIMjQuMVY1M2gtMS42NTR2LTguOTU1aC0zLjAwOHYtMS4xMjFIMjcuMTA3eiIvPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNMzYuNzA1LDQyLjkyNGgxLjY2OFY1M2gtMS42Njh2LTYuOTMybC0yLjI1Niw1LjYwNUgzM2wtMi4yNy01LjYwNVY1M2gtMS42NjhWNDIuOTI0aDEuNjY4ICAgIGwyLjk5NCw2Ljg5MUwzNi43MDUsNDIuOTI0eiIvPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNNDIuNTcsNDIuOTI0djguODMyaDQuNjM1VjUzaC02LjMwM1Y0Mi45MjRINDIuNTd6Ii8+Cgk8L2c+Cgk8Zz4KCQk8cGF0aCBzdHlsZT0iZmlsbDojRUM2NjMwOyIgZD0iTTIzLjIwNywxNi4yOTNjLTAuMzkxLTAuMzkxLTEuMDIzLTAuMzkxLTEuNDE0LDBsLTYsNmMtMC4zOTEsMC4zOTEtMC4zOTEsMS4wMjMsMCwxLjQxNGw2LDYgICAgQzIxLjk4OCwyOS45MDIsMjIuMjQ0LDMwLDIyLjUsMzBzMC41MTItMC4wOTgsMC43MDctMC4yOTNjMC4zOTEtMC4zOTEsMC4zOTEtMS4wMjMsMC0xLjQxNEwxNy45MTQsMjNsNS4yOTMtNS4yOTMgICAgQzIzLjU5OCwxNy4zMTYsMjMuNTk4LDE2LjY4NCwyMy4yMDcsMTYuMjkzeiIvPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNFQzY2MzA7IiBkPSJNNDEuMjA3LDIyLjI5M2wtNi02Yy0wLjM5MS0wLjM5MS0xLjAyMy0wLjM5MS0xLjQxNCwwcy0wLjM5MSwxLjAyMywwLDEuNDE0TDM5LjA4NiwyMyAgICBsLTUuMjkzLDUuMjkzYy0wLjM5MSwwLjM5MS0wLjM5MSwxLjAyMywwLDEuNDE0QzMzLjk4OCwyOS45MDIsMzQuMjQ0LDMwLDM0LjUsMzBzMC41MTItMC4wOTgsMC43MDctMC4yOTNsNi02ICAgIEM0MS41OTgsMjMuMzE2LDQxLjU5OCwyMi42ODQsNDEuMjA3LDIyLjI5M3oiLz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K" />
                                            </a>
                                        @endif
                                        @if($voucher->voucher_state_id >= \ElectronicInvoicing\StaticClasses\VoucherStates::SENDED)
                                            <a href="{{ route('vouchers.xml', $voucher) }}">
                                                <img alt="XML" height="32" width="32"  src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU2IDU2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1NiA1NjsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+CjxnPgoJPHBhdGggc3R5bGU9ImZpbGw6I0U5RTlFMDsiIGQ9Ik0zNi45ODUsMEg3Ljk2M0M3LjE1NSwwLDYuNSwwLjY1NSw2LjUsMS45MjZWNTVjMCwwLjM0NSwwLjY1NSwxLDEuNDYzLDFoNDAuMDc0ICAgYzAuODA4LDAsMS40NjMtMC42NTUsMS40NjMtMVYxMi45NzhjMC0wLjY5Ni0wLjA5My0wLjkyLTAuMjU3LTEuMDg1TDM3LjYwNywwLjI1N0MzNy40NDIsMC4wOTMsMzcuMjE4LDAsMzYuOTg1LDB6Ii8+Cgk8cG9seWdvbiBzdHlsZT0iZmlsbDojRDlEN0NBOyIgcG9pbnRzPSIzNy41LDAuMTUxIDM3LjUsMTIgNDkuMzQ5LDEyICAiLz4KCTxwYXRoIHN0eWxlPSJmaWxsOiNGMjlDMUY7IiBkPSJNNDguMDM3LDU2SDcuOTYzQzcuMTU1LDU2LDYuNSw1NS4zNDUsNi41LDU0LjUzN1YzOWg0M3YxNS41MzdDNDkuNSw1NS4zNDUsNDguODQ1LDU2LDQ4LjAzNyw1NnoiLz4KCTxnPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNMTkuMzc5LDQ4LjEwNUwyMS45MzYsNTNoLTEuOWwtMS42LTMuODAxaC0wLjEzN0wxNi41NzYsNTNoLTEuOWwyLjU1Ny00Ljg5NWwtMi43MjEtNS4xODJoMS44NzMgICAgbDEuNzc3LDQuMTAyaDAuMTM3bDEuOTI4LTQuMTAySDIyLjFMMTkuMzc5LDQ4LjEwNXoiLz4KCQk8cGF0aCBzdHlsZT0iZmlsbDojRkZGRkZGOyIgZD0iTTMxLjk5OCw0Mi45MjRoMS42NjhWNTNoLTEuNjY4di02LjkzMmwtMi4yNTYsNS42MDVoLTEuNDQ5bC0yLjI3LTUuNjA1VjUzaC0xLjY2OFY0Mi45MjRoMS42NjggICAgbDIuOTk0LDYuODkxTDMxLjk5OCw0Mi45MjR6Ii8+CgkJPHBhdGggc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIGQ9Ik0zNy44NjMsNDIuOTI0djguODMyaDQuNjM1VjUzaC02LjMwM1Y0Mi45MjRIMzcuODYzeiIvPgoJPC9nPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0xNS41LDI0Yy0wLjI1NiwwLTAuNTEyLTAuMDk4LTAuNzA3LTAuMjkzYy0wLjM5MS0wLjM5MS0wLjM5MS0xLjAyMywwLTEuNDE0bDYtNiAgIGMwLjM5MS0wLjM5MSwxLjAyMy0wLjM5MSwxLjQxNCwwczAuMzkxLDEuMDIzLDAsMS40MTRsLTYsNkMxNi4wMTIsMjMuOTAyLDE1Ljc1NiwyNCwxNS41LDI0eiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0yMS41LDMwYy0wLjI1NiwwLTAuNTEyLTAuMDk4LTAuNzA3LTAuMjkzbC02LTZjLTAuMzkxLTAuMzkxLTAuMzkxLTEuMDIzLDAtMS40MTQgICBzMS4wMjMtMC4zOTEsMS40MTQsMGw2LDZjMC4zOTEsMC4zOTEsMC4zOTEsMS4wMjMsMCwxLjQxNEMyMi4wMTIsMjkuOTAyLDIxLjc1NiwzMCwyMS41LDMweiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0zMy41LDMwYy0wLjI1NiwwLTAuNTEyLTAuMDk4LTAuNzA3LTAuMjkzYy0wLjM5MS0wLjM5MS0wLjM5MS0xLjAyMywwLTEuNDE0bDYtNiAgIGMwLjM5MS0wLjM5MSwxLjAyMy0wLjM5MSwxLjQxNCwwczAuMzkxLDEuMDIzLDAsMS40MTRsLTYsNkMzNC4wMTIsMjkuOTAyLDMzLjc1NiwzMCwzMy41LDMweiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0zOS41LDI0Yy0wLjI1NiwwLTAuNTEyLTAuMDk4LTAuNzA3LTAuMjkzbC02LTZjLTAuMzkxLTAuMzkxLTAuMzkxLTEuMDIzLDAtMS40MTQgICBzMS4wMjMtMC4zOTEsMS40MTQsMGw2LDZjMC4zOTEsMC4zOTEsMC4zOTEsMS4wMjMsMCwxLjQxNEM0MC4wMTIsMjMuOTAyLDM5Ljc1NiwyNCwzOS41LDI0eiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0YyOUMxRjsiIGQ9Ik0yNC41LDMyYy0wLjExLDAtMC4yMjMtMC4wMTktMC4zMzMtMC4wNThjLTAuNTIxLTAuMTg0LTAuNzk0LTAuNzU1LTAuNjEtMS4yNzZsNi0xNyAgIGMwLjE4NS0wLjUyMSwwLjc1My0wLjc5NSwxLjI3Ni0wLjYxYzAuNTIxLDAuMTg0LDAuNzk0LDAuNzU1LDAuNjEsMS4yNzZsLTYsMTdDMjUuMjk4LDMxLjc0NCwyNC45MTIsMzIsMjQuNSwzMnoiLz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K" />
                                            </a>
                                            <a href="{{ route('vouchers.pdf', $voucher) }}">
                                                <img alt="PDF" height="32" width="32"  src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU2IDU2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1NiA1NjsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+CjxnPgoJPHBhdGggc3R5bGU9ImZpbGw6I0U5RTlFMDsiIGQ9Ik0zNi45ODUsMEg3Ljk2M0M3LjE1NSwwLDYuNSwwLjY1NSw2LjUsMS45MjZWNTVjMCwwLjM0NSwwLjY1NSwxLDEuNDYzLDFoNDAuMDc0ICAgYzAuODA4LDAsMS40NjMtMC42NTUsMS40NjMtMVYxMi45NzhjMC0wLjY5Ni0wLjA5My0wLjkyLTAuMjU3LTEuMDg1TDM3LjYwNywwLjI1N0MzNy40NDIsMC4wOTMsMzcuMjE4LDAsMzYuOTg1LDB6Ii8+Cgk8cG9seWdvbiBzdHlsZT0iZmlsbDojRDlEN0NBOyIgcG9pbnRzPSIzNy41LDAuMTUxIDM3LjUsMTIgNDkuMzQ5LDEyICAiLz4KCTxwYXRoIHN0eWxlPSJmaWxsOiNDQzRCNEM7IiBkPSJNMTkuNTE0LDMzLjMyNEwxOS41MTQsMzMuMzI0Yy0wLjM0OCwwLTAuNjgyLTAuMTEzLTAuOTY3LTAuMzI2ICAgYy0xLjA0MS0wLjc4MS0xLjE4MS0xLjY1LTEuMTE1LTIuMjQyYzAuMTgyLTEuNjI4LDIuMTk1LTMuMzMyLDUuOTg1LTUuMDY4YzEuNTA0LTMuMjk2LDIuOTM1LTcuMzU3LDMuNzg4LTEwLjc1ICAgYy0wLjk5OC0yLjE3Mi0xLjk2OC00Ljk5LTEuMjYxLTYuNjQzYzAuMjQ4LTAuNTc5LDAuNTU3LTEuMDIzLDEuMTM0LTEuMjE1YzAuMjI4LTAuMDc2LDAuODA0LTAuMTcyLDEuMDE2LTAuMTcyICAgYzAuNTA0LDAsMC45NDcsMC42NDksMS4yNjEsMS4wNDljMC4yOTUsMC4zNzYsMC45NjQsMS4xNzMtMC4zNzMsNi44MDJjMS4zNDgsMi43ODQsMy4yNTgsNS42Miw1LjA4OCw3LjU2MiAgIGMxLjMxMS0wLjIzNywyLjQzOS0wLjM1OCwzLjM1OC0wLjM1OGMxLjU2NiwwLDIuNTE1LDAuMzY1LDIuOTAyLDEuMTE3YzAuMzIsMC42MjIsMC4xODksMS4zNDktMC4zOSwyLjE2ICAgYy0wLjU1NywwLjc3OS0xLjMyNSwxLjE5MS0yLjIyLDEuMTkxYy0xLjIxNiwwLTIuNjMyLTAuNzY4LTQuMjExLTIuMjg1Yy0yLjgzNywwLjU5My02LjE1LDEuNjUxLTguODI4LDIuODIyICAgYy0wLjgzNiwxLjc3NC0xLjYzNywzLjIwMy0yLjM4Myw0LjI1MUMyMS4yNzMsMzIuNjU0LDIwLjM4OSwzMy4zMjQsMTkuNTE0LDMzLjMyNHogTTIyLjE3NiwyOC4xOTggICBjLTIuMTM3LDEuMjAxLTMuMDA4LDIuMTg4LTMuMDcxLDIuNzQ0Yy0wLjAxLDAuMDkyLTAuMDM3LDAuMzM0LDAuNDMxLDAuNjkyQzE5LjY4NSwzMS41ODcsMjAuNTU1LDMxLjE5LDIyLjE3NiwyOC4xOTh6ICAgIE0zNS44MTMsMjMuNzU2YzAuODE1LDAuNjI3LDEuMDE0LDAuOTQ0LDEuNTQ3LDAuOTQ0YzAuMjM0LDAsMC45MDEtMC4wMSwxLjIxLTAuNDQxYzAuMTQ5LTAuMjA5LDAuMjA3LTAuMzQzLDAuMjMtMC40MTUgICBjLTAuMTIzLTAuMDY1LTAuMjg2LTAuMTk3LTEuMTc1LTAuMTk3QzM3LjEyLDIzLjY0OCwzNi40ODUsMjMuNjcsMzUuODEzLDIzLjc1NnogTTI4LjM0MywxNy4xNzQgICBjLTAuNzE1LDIuNDc0LTEuNjU5LDUuMTQ1LTIuNjc0LDcuNTY0YzIuMDktMC44MTEsNC4zNjItMS41MTksNi40OTYtMi4wMkMzMC44MTUsMjEuMTUsMjkuNDY2LDE5LjE5MiwyOC4zNDMsMTcuMTc0eiAgICBNMjcuNzM2LDguNzEyYy0wLjA5OCwwLjAzMy0xLjMzLDEuNzU3LDAuMDk2LDMuMjE2QzI4Ljc4MSw5LjgxMywyNy43NzksOC42OTgsMjcuNzM2LDguNzEyeiIvPgoJPHBhdGggc3R5bGU9ImZpbGw6I0NDNEI0QzsiIGQ9Ik00OC4wMzcsNTZINy45NjNDNy4xNTUsNTYsNi41LDU1LjM0NSw2LjUsNTQuNTM3VjM5aDQzdjE1LjUzN0M0OS41LDU1LjM0NSw0OC44NDUsNTYsNDguMDM3LDU2eiIvPgoJPGc+CgkJPHBhdGggc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIGQ9Ik0xNy4zODUsNTNoLTEuNjQxVjQyLjkyNGgyLjg5OGMwLjQyOCwwLDAuODUyLDAuMDY4LDEuMjcxLDAuMjA1ICAgIGMwLjQxOSwwLjEzNywwLjc5NSwwLjM0MiwxLjEyOCwwLjYxNWMwLjMzMywwLjI3MywwLjYwMiwwLjYwNCwwLjgwNywwLjk5MXMwLjMwOCwwLjgyMiwwLjMwOCwxLjMwNiAgICBjMCwwLjUxMS0wLjA4NywwLjk3My0wLjI2LDEuMzg4Yy0wLjE3MywwLjQxNS0wLjQxNSwwLjc2NC0wLjcyNSwxLjA0NmMtMC4zMSwwLjI4Mi0wLjY4NCwwLjUwMS0xLjEyMSwwLjY1NiAgICBzLTAuOTIxLDAuMjMyLTEuNDQ5LDAuMjMyaC0xLjIxN1Y1M3ogTTE3LjM4NSw0NC4xNjh2My45OTJoMS41MDRjMC4yLDAsMC4zOTgtMC4wMzQsMC41OTUtMC4xMDMgICAgYzAuMTk2LTAuMDY4LDAuMzc2LTAuMTgsMC41NC0wLjMzNWMwLjE2NC0wLjE1NSwwLjI5Ni0wLjM3MSwwLjM5Ni0wLjY0OWMwLjEtMC4yNzgsMC4xNS0wLjYyMiwwLjE1LTEuMDMyICAgIGMwLTAuMTY0LTAuMDIzLTAuMzU0LTAuMDY4LTAuNTY3Yy0wLjA0Ni0wLjIxNC0wLjEzOS0wLjQxOS0wLjI4LTAuNjE1Yy0wLjE0Mi0wLjE5Ni0wLjM0LTAuMzYtMC41OTUtMC40OTIgICAgYy0wLjI1NS0wLjEzMi0wLjU5My0wLjE5OC0xLjAxMi0wLjE5OEgxNy4zODV6Ii8+CgkJPHBhdGggc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIGQ9Ik0zMi4yMTksNDcuNjgyYzAsMC44MjktMC4wODksMS41MzgtMC4yNjcsMi4xMjZzLTAuNDAzLDEuMDgtMC42NzcsMS40NzdzLTAuNTgxLDAuNzA5LTAuOTIzLDAuOTM3ICAgIHMtMC42NzIsMC4zOTgtMC45OTEsMC41MTNjLTAuMzE5LDAuMTE0LTAuNjExLDAuMTg3LTAuODc1LDAuMjE5QzI4LjIyMiw1Mi45ODQsMjguMDI2LDUzLDI3Ljg5OCw1M2gtMy44MTRWNDIuOTI0aDMuMDM1ICAgIGMwLjg0OCwwLDEuNTkzLDAuMTM1LDIuMjM1LDAuNDAzczEuMTc2LDAuNjI3LDEuNiwxLjA3M3MwLjc0LDAuOTU1LDAuOTUsMS41MjRDMzIuMTE0LDQ2LjQ5NCwzMi4yMTksNDcuMDgsMzIuMjE5LDQ3LjY4MnogICAgIE0yNy4zNTIsNTEuNzk3YzEuMTEyLDAsMS45MTQtMC4zNTUsMi40MDYtMS4wNjZzMC43MzgtMS43NDEsMC43MzgtMy4wOWMwLTAuNDE5LTAuMDUtMC44MzQtMC4xNS0xLjI0NCAgICBjLTAuMTAxLTAuNDEtMC4yOTQtMC43ODEtMC41ODEtMS4xMTRzLTAuNjc3LTAuNjAyLTEuMTY5LTAuODA3cy0xLjEzLTAuMzA4LTEuOTE0LTAuMzA4aC0wLjk1N3Y3LjYyOUgyNy4zNTJ6Ii8+CgkJPHBhdGggc3R5bGU9ImZpbGw6I0ZGRkZGRjsiIGQ9Ik0zNi4yNjYsNDQuMTY4djMuMTcyaDQuMjExdjEuMTIxaC00LjIxMVY1M2gtMS42NjhWNDIuOTI0SDQwLjl2MS4yNDRIMzYuMjY2eiIvPgoJPC9nPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+Cjwvc3ZnPgo=" />
                                            </a>
                                        @endif
                                    </td>
                                    @unlessrole('customer')
                                        <td>
                                            @switch($voucher->voucher_state_id)
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::SAVED)
                                                    <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-sm">{{ __('view.edit') }}</a>
                                                    @break
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED)
                                                    @can('send_vouchers')
                                                        <form action="{{ route('vouchers.send', $voucher) }}" method="post">
                                                            {{ csrf_field() }}
                                                            <button type="submit" class="btn btn-sm btn-light">{{ __('view.send') }}</button>
                                                        </form>
                                                    @endcan
                                                    @break
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::REJECTED)
                                                    <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-sm">{{ __('view.edit') }}</a>
                                                    @can('send_vouchers')
                                                        <form action="{{ route('vouchers.send', $voucher) }}" method="post">
                                                            {{ csrf_field() }}
                                                            <button type="submit" class="btn btn-sm btn-light">{{ __('view.send') }}</button>
                                                        </form>
                                                    @endcan
                                                    @break
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::SENDED)

                                                    @break
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::RECEIVED)

                                                    @break
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::RETURNED)

                                                    @break
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::AUTHORIZED)
                                                    <a href="#">
                                                        <img alt="EMAIL" height="32" width="32" data-toggle="modal" data-target="#emailModal"
                                                            data-voucher="{{ $voucher->id }}" src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCA1MDIuMDczIDUwMi4wNzMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUwMi4wNzMgNTAyLjA3MzsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+CjxwYXRoIHN0eWxlPSJmaWxsOiNGRkQxNUM7IiBkPSJNNTAxLjgxMiw0ODEuMTc2YzAsNi4yNjktMi4wOSwxMS40OTQtNi4yNjksMTUuNjczYy00LjE4LDMuMTM1LTguMzU5LDUuMjI0LTE0LjYyOSw1LjIyNEgyMS4xNTkgIGMtNS4yMjQsMC0xMC40NDktMi4wOS0xNC42MjktNS4yMjRjLTQuMTgtNC4xOC02LjI2OS05LjQwNC02LjI2OS0xNS42NzNWMTc1LjAyaDUwMS41NTFWNDgxLjE3NnoiLz4KPHBhdGggc3R5bGU9ImZpbGw6I0Y4QjY0QzsiIGQ9Ik00OTUuNTQzLDQ5Ni44NDljLTQuMTgsMy4xMzUtOC4zNTksNS4yMjQtMTQuNjI5LDUuMjI0SDIxLjE1OWMtNS4yMjQsMC0xMC40NDktMi4wOS0xNC42MjktNS4yMjQgIGwyNDQuNTA2LTIxMC4wMjRMNDk1LjU0Myw0OTYuODQ5eiIvPgo8cGF0aCBzdHlsZT0iZmlsbDojNDA1OTZCOyIgZD0iTTUwMS44MTIsMTc1LjAybC03MS4wNTMsNTEuMkwyNjQuNjIsMzQ1LjMzOWMtOC4zNTksNi4yNjktMTkuODUzLDYuMjY5LTI4LjIxMiwwTDcxLjMxNCwyMjYuMjIgIGwtNzEuMDUzLTUxLjJsNzEuMDUzLTUxLjJMMjI1Ljk1OSwxMy4wNjFsMTEuNDk0LTguMzU5YzguMzU5LTYuMjY5LDE5Ljg1My02LjI2OSwyOC4yMTIsMGwxMS40OTQsOC4zNTlsNzQuMTg4LDUzLjI5bDM2LjU3MSwyNi4xMjIgIGw0Mi44NDEsMzEuMzQ3TDUwMS44MTIsMTc1LjAyeiIvPgo8cGF0aCBzdHlsZT0iZmlsbDojRjJGMkYyOyIgZD0iTTQzMC43NTksNzcuODQ1VjIyNi4yMkwyNjQuNjIsMzQ1LjMzOWMtOC4zNTksNi4yNjktMTkuODUzLDYuMjY5LTI4LjIxMiwwTDcxLjMxNCwyMjYuMjJWMzMuOTU5ICBjMC0xMS40OTQsOS40MDQtMjAuODk4LDIwLjg5OC0yMC44OThoMjcyLjcxOEw0MzAuNzU5LDc3Ljg0NXoiLz4KPHBhdGggc3R5bGU9ImZpbGw6I0NERDZFMDsiIGQ9Ik0zNjUuOTc1LDYxLjEyN2MwLDkuNDA0LDcuMzE0LDE2LjcxOCwxNi43MTgsMTYuNzE4aDQ4LjA2NWwtNjQuNzg0LTY0Ljc4NEwzNjUuOTc1LDYxLjEyNyAgTDM2NS45NzUsNjEuMTI3eiIvPgo8cGF0aCBzdHlsZT0iZmlsbDojRkY3MDU4OyIgZD0iTTMxMi42ODYsMjIzLjA4NmgxNi43MThjLTUuMjI0LDExLjQ5NC0xMy41ODQsMTkuODUzLTI1LjA3OCwyNi4xMjIgIGMtMTIuNTM5LDcuMzE0LTI4LjIxMiwxMC40NDktNDcuMDIsMTAuNDQ5Yy0xNy43NjMsMC0zMy40MzctMy4xMzUtNDUuOTc2LTkuNDA0Yy0xMi41MzktNi4yNjktMjIuOTg4LTE0LjYyOS0yOS4yNTctMjcuMTY3ICBjLTYuMjY5LTExLjQ5NC05LjQwNC0yNS4wNzgtOS40MDQtMzguNjYxYzAtMTUuNjczLDMuMTM1LTI5LjI1NywxMC40NDktNDIuODQxYzcuMzE0LTEzLjU4NCwxNi43MTgtMjIuOTg4LDI5LjI1Ny0yOS4yNTcgIGMxMi41MzktNi4yNjksMjcuMTY3LTkuNDA0LDQyLjg0MS05LjQwNGMxMy41ODQsMCwyNi4xMjIsMy4xMzUsMzYuNTcxLDguMzU5YzEwLjQ0OSw1LjIyNCwxOC44MDgsMTIuNTM5LDI0LjAzMywyMi45ODggIGM1LjIyNCw5LjQwNCw4LjM1OSwyMC44OTgsOC4zNTksMzIuMzkyYzAsMTMuNTg0LTQuMTgsMjYuMTIyLTEyLjUzOSwzNy42MTZjLTEwLjQ0OSwxNC42MjktMjQuMDMzLDIwLjg5OC00MC43NTEsMjAuODk4ICBjLTQuMTgsMC04LjM1OS0xLjA0NS0xMC40NDktMi4wOWMtMi4wOS0yLjA5LTQuMTgtNC4xOC00LjE4LTcuMzE0Yy02LjI2OSw2LjI2OS0xMy41ODQsOS40MDQtMjEuOTQzLDkuNDA0ICBjLTkuNDA0LDAtMTYuNzE4LTMuMTM1LTIxLjk0My05LjQwNGMtNi4yNjktNi4yNjktOS40MDQtMTQuNjI5LTkuNDA0LTI1LjA3OGMwLTEyLjUzOSwzLjEzNS0yNC4wMzMsMTAuNDQ5LTM1LjUyNyAgYzguMzU5LTEyLjUzOSwxOS44NTMtMTguODA4LDMzLjQzNy0xOC44MDhjOS40MDQsMCwxNi43MTgsNC4xOCwyMS45NDMsMTEuNDk0bDIuMDktOS40MDRoMjEuOTQzbC0xMi41MzksNTguNTE0ICBjLTEuMDQ1LDQuMTgtMS4wNDUsNi4yNjktMS4wNDUsNy4zMTRzMCwyLjA5LDEuMDQ1LDMuMTM1czEuMDQ1LDEuMDQ1LDIuMDksMS4wNDVjMi4wOSwwLDYuMjY5LTIuMDksMTAuNDQ5LTUuMjI0ICBjNS4yMjQtNC4xOCwxMC40NDktOS40MDQsMTMuNTg0LTE2LjcxOGMzLjEzNS03LjMxNCw1LjIyNC0xNC42MjksNS4yMjQtMjEuOTQzYzAtMTMuNTg0LTUuMjI0LTI1LjA3OC0xNC42MjktMzMuNDM3ICBjLTkuNDA0LTkuNDA0LTIyLjk4OC0xMy41ODQtNDAuNzUxLTEzLjU4NGMtMTQuNjI5LDAtMjcuMTY3LDMuMTM1LTM3LjYxNiw5LjQwNGMtMTAuNDQ5LDYuMjY5LTE3Ljc2MywxNC42MjktMjIuOTg4LDI1LjA3OCAgcy03LjMxNCwyMS45NDMtNy4zMTQsMzQuNDgyYzAsMTEuNDk0LDMuMTM1LDIxLjk0Myw4LjM1OSwzMS4zNDdjNi4yNjksOS40MDQsMTMuNTg0LDE2LjcxOCwyNC4wMzMsMjAuODk4ICBjMTAuNDQ5LDQuMTgsMjEuOTQzLDYuMjY5LDM1LjUyNyw2LjI2OWMxMi41MzksMCwyNC4wMzMtMi4wOSwzMy40MzctNS4yMjRDMjk5LjEwMiwyMzUuNjI0LDMwNi40MTYsMjMwLjQsMzEyLjY4NiwyMjMuMDg2eiAgIE0yMjMuODY5LDE4OS42NDljMCw3LjMxNCwxLjA0NSwxMS40OTQsNC4xOCwxNS42NzNjMy4xMzUsMy4xMzUsNi4yNjksNS4yMjQsMTAuNDQ5LDUuMjI0YzMuMTM1LDAsNi4yNjktMS4wNDUsOC4zNTktMi4wOSAgYzIuMDktMS4wNDUsNC4xOC0zLjEzNSw2LjI2OS01LjIyNGMzLjEzNS0zLjEzNSw1LjIyNS04LjM1OSw3LjMxNC0xNC42MjljMi4wOS02LjI2OSwzLjEzNS0xMi41MzksMy4xMzUtMTcuNzYzICBjMC02LjI2OS0xLjA0NS0xMC40NDktNC4xOC0xNC42MjljLTMuMTM1LTMuMTM1LTYuMjY5LTUuMjI0LTEwLjQ0OS01LjIyNGMtNC4xOCwwLTkuNDA0LDIuMDktMTIuNTM5LDUuMjI0ICBjLTQuMTgsMy4xMzUtNy4zMTQsOC4zNTktOS40MDQsMTUuNjczQzIyNC45MTQsMTc4LjE1NSwyMjMuODY5LDE4NC40MjQsMjIzLjg2OSwxODkuNjQ5eiIvPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K" />
                                                    </a>
                                                    @break
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::IN_PROCESS)

                                                    @break
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::UNAUTHORIZED)

                                                    @break
                                                @case(\ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED)

                                                    @break
                                            @endswitch
                                        </td>
                                    @endunlessrole
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@unlessrole('customer')
    <div class="modal fade" tabindex="-1" role="dialog" id="emailModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <strong><p>Send voucher by email</p></strong>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="modal-form" action="#" method="post">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <input type="hidden" id="voucher" name="voucher" value="">
                        <div class="form-group">
                            <label for="email">{{ __('view.email') }}</label>
                            <input class="form-control" type="email" id="email" name="email" value="" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">{{ __('view.close') }}</button>
                        <button id="submit_email" type="button" class="btn btn-sm btn-primary">Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('layouts.validation')

@endunlessrole
@endsection
