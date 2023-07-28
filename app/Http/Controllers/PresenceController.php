<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentPresentSemesterExport;
use App\Http\Resources\LaporanPresensiResource;

class PresenceController extends Controller
{
    public function index(Request $request)
    {
        $classroomRequest = $request->query('classroom');
        $date = $request->query('date');
        $period = $request->query('period');
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfDay();

        if (empty($classroomRequest) && empty($period) && empty($date)) {
            $data = Student::whereHas('presencesToday')->orWhereHas('absencesToday')->latest()->get();
        } else {
            $data = Student::when($classroomRequest !== 'all' || $classroomRequest === null, function ($query) use ($classroomRequest) {
                return $query->where('class_id', $classroomRequest);
            })
            ->when($period === 'today', function ($query) {
                return $query->whereHas('presencesToday')->orWhereHas('absencesToday')->latest();
            })
            ->when($period === 'ondate', function ($query) use ($date) {
                return $query->whereHas('presences', function ($filter) use ($date) {
                    return $filter->whereDate('presence_in', $date)->latest();
                })->orWhereHas('absences', function ($filter) use ($date) {
                    return $filter->whereDate('absence_date', $date)->latest();
                });
            })
            ->get();
        }
        
        $classroom = Classroom::get();
        return view('pages.presence.index', compact('data', 'classroom', 'date'));
    }

    public function getStudent($classroom)
    {
        $data = Student::where('class_id', $classroom)->get();
        return response()->json($data, 200);
    }

    public function perStudent(Request $request)
    {
        $classroom = Classroom::get();
        $nipd = $request->query('student');
        $period = $request->query('period');

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totals = [
            'hadir' => 0,
            'sakit' => 0,
            'izin' => 0,
            'tanpa keterangan' => 0,
        ];


        if ($period == 'semester1') {
            $semester = 'Semester 1';
            $start_date = Carbon::create($currentYear, 7, 1);
            $end_date = Carbon::create($currentYear, 12, 30);
        } elseif ($period == 'semester2') {
            $semester = 'Semester 2';
            $year_next = $currentYear + 1;
            $start_date = Carbon::create($year_next, 1, 1);
            $end_date = Carbon::create($year_next, 6, 30);
        }

        if (empty($period)) {
            $data = $this->getTotalAttendanceToday($nipd);
            foreach ($data as $item) {
                if ($item->status === 'hadir') {
                    $totals['hadir']++;
                } elseif ($item->status === 'sakit') {
                    $totals['sakit']++;
                } elseif ($item->status === 'izin') {
                    $totals['izin']++;
                } elseif ($item->status === 'tanpa keterangan') {
                    $totals['tanpa keterangan']++;
                }
            }
        } else if (!empty($period) && !empty($nipd)) {
            $data = $this->getTotalAttendance($nipd, $start_date, $end_date, false);
            foreach ($data as $item) {
                if ($item->status === 'hadir') {
                    $totals['hadir']++;
                } elseif ($item->status === 'sakit') {
                    $totals['sakit']++;
                } elseif ($item->status === 'izin') {
                    $totals['izin']++;
                } elseif ($item->status === 'tanpa keterangan') {
                    $totals['tanpa keterangan']++;
                }
            }

        } else {
            $data = [];
        }

        
        $studentData = Student::where('nipd', $nipd)->first();
        if ($request->has('isDownload') && $request->query('isDownload') == 'true') {
            return Excel::download(new StudentPresentSemesterExport($data, $studentData, $totals, $semester), 'data.xlsx');
        } else {
            return view('pages.presence.student', compact('data', 'classroom', 'studentData', 'totals'));
        }
    }

    function download($data, $studentData, $totals, $semester)
    {
        return Excel::download(new StudentPresentSemesterExport($data, $studentData, $totals, $semester), 'data.xlsx');
    }


    function getDataUnion($nipd, $start_date, $end_date, $isToday)
    {
        $data = DB::table('presences')
            ->select('presence_in as tanggal', DB::raw("'hadir' as status"))
            ->where('nipd', $nipd);

        $dataAbsences = DB::table('absences')
            ->select('absence_date as tanggal', DB::raw("
                CASE
                    WHEN absence_type = 'sakit' THEN 'sakit'
                    WHEN absence_type = 'izin' THEN 'izin'
                    WHEN absence_type = 'tanpa_keterangan' THEN 'tanpa keterangan'
                END as status
            "))
            ->where('nipd', $nipd);

        if ($isToday) {
            $data->whereDate('presence_in', today());
            $dataAbsences->whereDate('absence_date', today());
        } else {
            $data->whereBetween('presence_in', [$start_date, $end_date]);
            $dataAbsences->whereBetween('absence_date', [$start_date, $end_date]);
        }

        return $data->unionAll($dataAbsences);
    }

    function getTotalAttendance($nipd, $start_date, $end_date)
    {
        $dataUnion = $this->getDataUnion($nipd, $start_date, $end_date, false);

        $result = DB::query()->fromSub($dataUnion, 'union_data')
            ->orderBy('tanggal')
            ->get();

        return $result;
    }

    function getTotalAttendanceToday($nipd)
    {
        $dataUnion = $this->getDataUnion($nipd, null, null, true);

        $result = DB::query()->fromSub($dataUnion, 'union_data')
            ->orderBy('tanggal')
            ->get();

        return $result;
    }
}
