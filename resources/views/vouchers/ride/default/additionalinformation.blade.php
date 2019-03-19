@if(count($voucher->additionalFields) > 0)
    <div class="card border-dark mb-3">
        <table class="table table-sm">
            <thead>
                <th class="align-middle" colspan="2">INFORMACIÓN ADICIONAL</th>
            </thead>
            <tbody>
            @foreach ($voucher->additionalFields as $additionalField)
                <tr>
                  <th class="align-middle">{{ $additionalField->name }}</th>
                  <td class="align-middle">{{ $additionalField->value }}</td>
                </tr>
            @endforeach
          </tbody>
        </table>
    </div>
@endif
