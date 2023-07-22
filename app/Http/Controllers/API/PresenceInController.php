<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Absence;
use App\Models\Presence;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\PresensiValidation;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PresenceInController extends Controller
{
    public function check()
    {
        $student = Auth::guard('api')->user()->student->nipd;
        $validatePresensi = PresensiValidation::validatePresensiMasuk($student);
        if ($validatePresensi === 222) {
            $statusCode = 222;
            $msg = "Anda sudah melakukan presensi hari ini";
        } else if ($validatePresensi === 223) {
            $statusCode = 223;
            $absenceType = Absence::where('nipd', $student)->whereDate('absence_date', today())->value('absence_type');
            $msg = "Anda tercatat sudah melaporkan {$absenceType} untuk hari ini";
        } else if ($validatePresensi === 224) {
            $statusCode = 224;
            $msg = "Anda tercatat tidak mengikuti kelas hari ini tanpa keterangan";
        } else {
            $statusCode = 200;
            $msg = "Anda berhak mengikuti kelas";
        }
        return response()->json([
            'status' => 'success',
            'code' => $statusCode,
            'message' => $msg
        ], 200);
    }

    public function storeOffline(Request $request)
    {
        DB::beginTransaction();
        $presence_in = Carbon::now();
        try {
            $student = Auth::guard('api')->user()->student;
            if ($request->has('override') && $request->query('override') === 'true') {
                $deleteAbsence = Absence::where('nipd', $student->nipd)->whereDate('absence_date', today())->delete();
            }
            $validateSchedule = PresensiValidation::scheduleMasukValidator($student->classroom->type);
            $presence = Presence::create([
                'nipd' => $student->nipd,
                'learning_type' => 'offline',
                'presence_in' => $presence_in,
                'presence_in_note' => $validateSchedule
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $presence
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'data' => $e->getMessage()
            ], 422);
        }
    }

    public function storeOnline(Request $request)
    {
        DB::beginTransaction();
        $presence_in = Carbon::now();
        try {
            $student = Auth::guard('api')->user()->student;
            $faceRecog = $this->faceRecog($request);
            if (!$faceRecog) {
                throw new \Exception('Verifikasi wajah gagal');
                return false;
            } else {
                if ($request->has('override') && $request->query('override') === 'true') {
                    $deleteAbsence = Absence::where('nipd', $student->nipd)->whereDate('absence_date', today())->delete();
                }
                $validateSchedule = PresensiValidation::scheduleMasukValidator($student->classroom->type);
                $presence = Presence::create([
                    'nipd' => $student->nipd,
                    'learning_type' => 'online',
                    'presence_in' => $presence_in,
                    'presence_in_note' => $validateSchedule
                ]);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'data' => $presence
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 200);
        }
    }

    

    public function faceRecog(Request $request)
    {
        $imageSource = $request->file('photo');
        $filename = Str::random(8) . '.' . $imageSource->getClientOriginalExtension();
        $saveImageSource = $imageSource->storeAs('public/temp', $filename);
        $sourceUrl = url($saveImageSource);
        $sourceUrl = str_replace('/public', '', $sourceUrl);

        //get dataset path from backend
        $studentPhoto = Auth::guard('api')->user()->student;
        $datasetUrl = url(Storage::url('photo/' . $studentPhoto->nipd . '/' . $studentPhoto->photo));

        //define data for post requeest
        $data = [
            'img1_path' => $sourceUrl,
            'img2_path' => $datasetUrl,
            'model_name' => 'Facenet',
            'detector_backend' => 'ssd',
            'distance_metric' => 'euclidean_l2'
        ];
        $url = config('app.face_recog_url') . '/verify';
        $response = Http::post($url, $data);
        $responseData = $response->json();
        if ($response->serverError) {
            Log::error('Verifikasi wajah gagal:');
            throw new \Exception('Verifikasi wajah gagal');
            return false;
        }
        Log::info('Response Data:', $responseData);
        Storage::delete('public/temp/'.$filename);
        if (!$responseData || $responseData['verified'] === 'False') {
            Log::error('Verifikasi wajah gagal:', $responseData);
            throw new \Exception('Verifikasi wajah gagal');
            return false;
        } else {
            $verified = true;
            return true;
        }
    }
}
