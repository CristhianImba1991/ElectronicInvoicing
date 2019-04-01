@if(count($voucher->additionalFields) > 0)
    <div style="padding: 2px 2px 0px 2px" class="card border-dark mb-3">
        <table class="table table-sm">
            <thead>
                <th class="align-middle" colspan="2">INFORMACIÓN ADICIONAL</th>
            </thead>
            <tbody>
            @foreach ($voucher->additionalFields as $additionalField)
                <tr>
                  <th class="align-middle">{{ $additionalField->name }}</th>
                  <td class="align-middle">{{ $additionalField->name === 'Email' ? str_replace(',', ', ', $additionalField->value) : $additionalField->value }}</td>
                </tr>
            @endforeach
          </tbody>
        </table>
    </div>
@endif
