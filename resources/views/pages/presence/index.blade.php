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
        <h4 class="card-title">Laporan Presensi Hari Ini</h4>
        <div>
            {{-- <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">Edit
                Radius</button> --}}
        </div>
    </div>
@endsection

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
    <x-modal title="Tambah Data Lokasi" idModal="addModal">
        <form id="formEditRadius" action="{{ route('perimeter.store') }}" method="POST">
            {{ csrf_field() }}
            <x-input-field label="Radius (Km)" type="number" name="radius"></x-input-field>
            <input type="submit" value="Simpan Data" class="btn btn-primary w-100">
        </form>
    </x-modal>

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
                            
                            if ($item->presencesToday) {
                                $date = \Carbon\Carbon::parse($item->presencesToday->presence_in)->format('D, d M Y');
                                $status = 'Hadir';
                                $class = 'bg-primary';
                            } elseif ($item->absencesToday) {
                                $data = \Carbon\Carbon::parse($item->absencesToday->absence_date)->format('D, d M Y');
                                $class = 'bg-danger';
                                $status = $item->absencesToday->absence_type;
                            } else {
                                $date = '';
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            {{-- <td>{{ $date }} --}}
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
@endpushOnce
