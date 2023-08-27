<div class="page-heading">
    <div class="page-title">
        <div class="row">
            {{-- <div class="col-12 col-md-6 order-md-1 order-last"> --}}
            <div class="d-flex justify-content-between w-100 mb-4">
                <h3>{{ $title }}</h3>
                @if (isset($extraButton) && !empty($extraButton))
                    {!! $extraButton !!}
                @endif
                {{-- </div> --}}
            </div>
        </div>
    </div>
