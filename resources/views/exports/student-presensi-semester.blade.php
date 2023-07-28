<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <table>
        <tr>
            <td>Rekap Presensi Siswa {{ $semester }}</td>
        </tr>
        <tr>
            <td>Nama Lengkap</td>
            <td>{{ $studentData->name }}</td>
        </tr>
        <tr>
            <td>NIPD</td>
            <td>{{ $studentData->nipd }}</td>
        </tr>
        <tr>
            <td>KELAS</td>
            <td>{{ $studentData->classroom->class_name }} - {{ $studentData->classroom->type }}</td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('D, d M Y') }}</td>
                    <td>{{ $item->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table>
        @foreach ($totals as $index => $item)
            <tr>
                <td>Total {{ $index }}</td>
                <td>{{ $item }}</td>
            </tr>
        @endforeach
    </table>
</body>

</html>
