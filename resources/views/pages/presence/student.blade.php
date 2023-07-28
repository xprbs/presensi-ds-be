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
            {{-- {{ $date !== null ? \Carbon\Carbon::parse($date)->format('d M Y') : 'Hari Ini' }} --}}
        </h4>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                Filter Data
            </button>
        </div>
    </div>
@endsection

<x-modal title="Filter Data" idModal="addModal">
    <form id="formEditRadius" action="{{ route('presence.student') }}" method="GET">
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
            <label for="classroom" class="form-label">Pilih Siswa</label>
            <select name="student" id="student" class="form-control" required>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="period" class="form-label">Periode</label>
            <select name="period" id="period" class="form-control">
                <option value="semester1">Semester 1</option>
                <option value="semester2">Semester 2</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="isDownload" class="form-label">Download</label>
            <select name="isDownload" id="isDownload" class="form-control">
                <option value="true">Download Excel</option>
                <option value="false">Tampilkan Saja</option>
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
    <div class="row gy-5">
        <div class="col-lg-12 mb-12">
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
                            if ($item->status == 'hadir') {
                                $class = 'bg-primary';
                            } else {
                                $class = 'bg-danger';
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('D, d M Y') }}
                            </td>
                            <td>{{ $studentData->nipd }}</td>
                            <td>{{ $studentData->name }}</td>
                            <td>{{ $studentData->classroom->class_name }} - {{ $studentData->classroom->type }}</td>
                            <td>
                                <span class="badge text-capitalize {{ $class }}">{{ $item->status }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-lg-12 mt-14">
            <table class="table table-bordered">
                @foreach ($totals as $index => $item)
                    <tr>
                        <th>Total {{ $index }}</th>
                        <td>{{ $item }}</td>
                    </tr>
                @endforeach
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
            let token = "{{ csrf_token() }}"
            $("#classroom").on('change', function(e) {
                e.preventDefault();
                let class_id = $(this).find(':selected').val();
                let url = "{{ route('presence.getStudent', ['classroom' => ':classroom']) }}"
                url = url.replace(':classroom', class_id)
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function(response) {
                        $('#student').empty();
                        response.forEach(value => {
                            $('#student').append($('<option>', {
                                value: value.nipd,
                                text: value.name
                            }));
                        });
                    },
                    error: function(error) {
                        console.log(error)
                    }
                });
            })
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
