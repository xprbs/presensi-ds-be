<?php

namespace App\Http\Controllers\Api;

use App\Models\Absence;
use App\Models\Student;
use App\Models\Presence;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\StudentListResource;

class StudentDataController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('nipd') && $request->query('nipd')) {
            return $this->detail($request);
        } else {
            $classroom = Auth::guard('api')->user()->teacher->class_id;
            $className = Auth::guard('api')->user()->teacher->classroom->class_name . ' - ' . Auth::guard('api')->user()->teacher->classroom->type;
            $data = Student::with(['user', 'classroom'])->where('class_id', $classroom)->orderBy('name', 'asc')->get();
            return response()->json([
                'status' => 'success',
                'classroom' => $className,
                'data' => StudentListResource::collection($data)
            ], 200);
        }
    }

    public function detail(Request $request)
    {
        $classroom = Auth::guard('api')->user()->teacher->class_id;
        $nipd = $request->query('nipd');
        
        $totalPresence = Presence::where('nipd', $nipd)->semester()->count();
        $totalAbsencesDueToPermission = Absence::where('nipd', $nipd)->semester()->count();
        $totalAbsencesDueToSickness = Absence::where('nipd', $nipd)->sickness()->semester()->count();
        $totalAbsencesWithoutNote = Absence::where('nipd', $nipd)->withoutNotes()->semester()->count();

        $rekapData = [
            'total_presences' => $totalPresence,
            'total_sickness' => $totalAbsencesDueToSickness,
            'total_due_permissions' => $totalAbsencesDueToPermission,
            'total_without_notes' => $totalAbsencesWithoutNote,
        ];

        $data = Student::with(['user', 'classroom'])->where('class_id', $classroom)->where('nipd', $nipd)->first();
        return response()->json([
            'status' => 'success',
            'data' => new StudentListResource($data),
            'recap' => $rekapData
        ], 200);
    }
}
