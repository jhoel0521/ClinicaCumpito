{{--
    Shared PDF header — included in all PDF views.
    Expects: $clinic (ClinicSetting), $consultation (Consultation with patient + doctor loaded)
--}}
<table style="width: 100%; border-collapse: collapse; margin-bottom: 0">
    <tr>
        @if ($clinic->logo_path && file_exists(storage_path('app/public/' . $clinic->logo_path)))
            <td style="width: 80px; vertical-align: middle; padding-right: 12px">
                @php
                    $logoData = base64_encode(file_get_contents(storage_path('app/public/' . $clinic->logo_path)));
                    $logoMime = mime_content_type(storage_path('app/public/' . $clinic->logo_path));
                @endphp

                <img
                    src="data:{{ $logoMime }};base64,{{ $logoData }}"
                    style="max-height: 60px; max-width: 72px; object-fit: contain"
                />
            </td>
        @endif

        <td style="vertical-align: middle">
            <div style="font-size: 17px; font-weight: bold; color: #0d7b5c">{{ $clinic->name }}</div>
            @if ($clinic->address)
                <div style="font-size: 10px; color: #555; margin-top: 2px">{{ $clinic->address }}</div>
            @endif

            @if ($clinic->phone || $clinic->whatsapp)
                <div style="font-size: 10px; color: #555; margin-top: 1px">
                    @if ($clinic->phone)
                        Tel: {{ $clinic->phone }}
                    @endif

                    @if ($clinic->phone && $clinic->whatsapp)
                        &nbsp;·&nbsp;
                    @endif

                    @if ($clinic->whatsapp)
                        WhatsApp: {{ $clinic->whatsapp }}
                    @endif
                </div>
            @endif
        </td>
        <td style="text-align: right; vertical-align: top; font-size: 10px; color: #555; white-space: nowrap">
            {{ $consultation->consultation_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}
        </td>
    </tr>
</table>

<hr style="border: none; border-top: 2px solid #0d7b5c; margin: 8px 0 6px" />

{{-- Doctor row --}}
@if ($consultation->doctor)
    <div style="font-size: 11px; color: #333">
        <strong>{{ $consultation->doctor->full_name }}</strong>
        @if ($consultation->doctor->specialty)
            &nbsp;·&nbsp; {{ $consultation->doctor->specialty }}
        @endif

        @if ($consultation->doctor->license_number)
            &nbsp;·&nbsp; Mat. {{ $consultation->doctor->license_number }}
        @endif
    </div>
@endif

<hr style="border: none; border-top: 1px solid #d1e8e2; margin: 6px 0 10px" />

{{-- Patient row --}}
<table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 10px">
    <tr>
        <td style="padding: 3px 6px 3px 0; color: #555">Paciente:</td>
        <td style="padding: 3px 6px; font-weight: bold">{{ $consultation->patient?->full_name ?? '—' }}</td>
        <td style="padding: 3px 0 3px 6px; color: #555; text-align: right">
            @if ($consultation->patient?->date_of_birth)
                @php
                    $months = (int) \Carbon\Carbon::parse($consultation->patient->date_of_birth)->diffInMonths(now());
                    $years = (int) floor($months / 12);
                    $rem = $months % 12;
                    $ageStr = $years > 0 ? "{$years} año(s)" : '';
                    $ageStr .= $years > 0 && $rem > 0 ? " {$rem} mes(es)" : ($years === 0 ? "{$rem} mes(es)" : '');
                @endphp

                Edad: {{ $ageStr }}
            @endif
        </td>
    </tr>
</table>
