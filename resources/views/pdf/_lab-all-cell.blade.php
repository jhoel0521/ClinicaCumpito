{{-- $lab: LaboratoryRequest, $examName: string --}}
<div class="exam-col-header">
    {{ $examName }}
    @if ($lab->status === 'received')
        <span
            style="
                float: right;
                font-size: 8px;
                background: #d1fae5;
                color: #065f46;
                padding: 1px 4px;
                border-radius: 2px;
            "
        >
            REC.
        </span>
    @endif
</div>

@if ($lab->status === 'pending')
    @if ($lab->items->isEmpty())
        <div class="param-item" style="color: #9ca3af; font-style: italic">Sin parámetros.</div>
    @endif

    @foreach ($lab->items as $item)
        <div class="param-item">○&nbsp;{{ $item->parameter_name ?: '(examen completo)' }}</div>
    @endforeach
@endif

@if ($lab->status !== 'pending')
    @foreach ($lab->items as $item)
        <div class="param-item" style="font-weight: bold">
            ✓&nbsp;{{ $item->parameter_name ?: '(examen completo)' }}
        </div>
        @foreach ($item->results as $result)
            <div class="{{ $result->is_abnormal ? 'result-abnormal' : 'result-normal' }}">
                {{ $result->parameter_name ?: '—' }}:
                {{ $result->value ?: '—' }}
                @if ($result->reference_range)
                    (ref: {{ $result->reference_range }})
                @endif

                @if ($result->is_abnormal)
                    ⚠
                @endif
            </div>
        @endforeach
    @endforeach
@endif

@if ($lab->presumptive_diagnosis)
    <div class="col-dx">
        <strong>Dx:</strong>
        {{ $lab->presumptive_diagnosis }}
    </div>
@endif

<div class="col-obs">
    <strong>Obs:</strong>
    {{ $lab->observations ?: '______________________' }}
</div>
