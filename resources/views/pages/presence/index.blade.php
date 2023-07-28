@pushOnce('custom-style')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/sweetalert2/sweetalert2.min.css') }}">
@endPushOnce

@php
    $sessionMsg = \Session::get('successMsg') ?? \Session::get('errorMsg');
@endphp

@extends('layouts.default', ['title' => 'Laporan Presensi', 'cardTitle' => ''])

@section('card-title')
    <div class="card-header d-flex justify-content-between">
        <h4 class="card-title">Laporan Presensi
            {{ $date !== null ? \Carbon\Carbon::parse($date)->format('d M Y') : 'Hari Ini' }}</h4>
        <div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">Filter
                Data</button>
        </div>
    </div>
@endsection

<x-modal title="Filter Data" idModal="addModal">
    <form id="formEditRadius" action="{{ route('presence.index') }}" method="GET">
        <div class="form-group mb-3">
            <label for="classroom" class="form-label">Kelas</label>
            <select name="classroom" id="classroom" class="form-control">
                <option value="all" selected>Semua</option>
                @foreach ($classroom as $class)
                    <option value="{{ $class->id }}">{{ $class->class_name }} - {{ $class->type }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="period" class="form-label">Periode</label>
            <select name="period" id="period" class="form-control">
                <option value="today" selected>Hari Ini</option>
                <option value="ondate">Berdasarkan Tanggal</option>
            </select>
        </div>
        <div id="datepicker" class="d-none">
            <x-input-field label="Pilih Tanggal" type="date" name="date"></x-input-field>
        </div>
        <input type="submit" value="Filter Data" class="btn btn-primary w-100">
    </form>
</x-modal>

@if ($sessionMsg)
    @section('alert-section')
        <div class="alert alert-{{ \Session::has('successMsg') ? 'success' : 'danger' }} alert-dismissible fade show"
            role="alert">
            {{ $sessionMsg }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endsection
@endif

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <table class="table" id="table1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>TANGGAL</th>
                        <th>NIPD</th>
                        <th>NAMA</th>
                        <th>KELAS</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $item)
                        @php
                            if ($item->presencesWeb) {
                                $date = $item->presencesWeb->presence_in;
                                $status = 'Hadir';
                                $class = 'bg-primary';
                            } elseif ($item->absencesWeb) {
                                $date = $item->absencesWeb->absence_in;
                                $class = 'bg-danger';
                                $status = $item->absencesWeb->absence_type;
                            } else {
                                $date = 'Belum Hadir';
                                $class = 'bg-secondary';
                                $status = 'Belum Hadir';
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}
                            </td>
                            <td>{{ $item->nipd }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->classroom->class_name }} - {{ $item->classroom->type }}</td>
                            <td>
                                <span class="badge text-capitalize {{ $class }}">{{ $status }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@pushOnce('custom-script')
    <script src="{{ asset('assets/extensions/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-1.12.1/datatables.min.js"></script>
    <script src="{{ asset('assets/js/pages/datatables.js') }}"></script>
    <script src="{{ asset('assets/extensions/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/components/delete-dialog.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#period").on("change", function(e) {
                e.preventDefault();
                let selectedPeriod = $(this).find(":selected").val();
                if (selectedPeriod !== "ondate") {
                    $("#datepicker").removeClass("d-none");
                    $("#datepicker").addClass("d-none");
                } else {
                    $("#datepicker").removeClass("d-none");
                    $("#datepicker").addClass("d-block");
                }
            })
        });
    </script>
@endpushOnce
