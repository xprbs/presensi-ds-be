<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Absence;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AbsenceController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $nipd = Auth::guard('api')->user()->student->nipd;
            $absence_type = $request->absence_type;

            $absence_date = Carbon::parse($request->absence_date)->toDateTimeString();

            $isExists = Absence::where('nipd', $nipd)->where('absence_date', $absence_date)->first();
            if ($isExists) {
                throw new \Exception("Anda telah mengirimkan laporan pada tanggal yang sama", 422);
            }
            
            $image = $request->file('attachment');
            $filename = $request->absence_date.'-'.Str::random(8) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('/public/absence/'.$absence_type.'/'.$nipd, $filename);

            $storeData = Absence::create([
                'nipd' => $nipd,
                'absence_type' => $request->absence_type,
                'absence_note' => $request->absence_note,
                'absence_date' => $absence_date,
                'attachment' => $filename
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan telah terkirim'
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
