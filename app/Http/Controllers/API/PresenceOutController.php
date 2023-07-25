<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Presence;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PresenceOutController extends Controller
{
    public function check()
    {
        $isExists = Presence::where('nipd', Auth::guard('api')->user()->student->nipd)->whereDate('presence_in', today())->where('presence_out', null)->first();
        if ($isExists) {
            return response()->json([
                'status' => 'success',
                'data' => $isExists
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'data' => $isExists
            ]);
        }
    }

    public function storeOffline(Request $request)
    {
        DB::beginTransaction();
        try {
            $presenceData = Presence::where('nipd', Auth::guard('api')->user()->student->nipd)->whereDate('presence_in', today())->first();
            $presenceData->presence_out = Carbon::now();
            if ($request->has('override') && $request->query('override') === 'true') {
                $presenceData->presence_out_note = 'Pulang lebih awal';
            }
            $presenceData->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $presenceData
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'data' => $e->getMessage()
            ], 200);
        }
    }

    public function storeOnline(Request $request)
    {
        DB::beginTransaction();
        try {
            $presenceData = Presence::where('nipd', Auth::guard('api')->user()->student->nipd)->whereDate('presence_in', today())->first();
            $faceRecog = $this->faceRecog($request);
            if (!$faceRecog) {
                throw new \Exception('Verifikasi wajah gagal');
                return false;
            } else {
                $presenceData->presence_out = Carbon::now();
                if ($request->has('override') && $request->query('override') === 'true') {
                    $presenceData->presence_out_note = 'Pulang lebih awal';
                }
                $presenceData->save();
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'data' => $presenceData
                ], 201);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'data' => $e->getMessage()
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
        if (!$response->ok()) {
            Log::error('Verifikasi wajah gagal:');
            throw new \Exception('Verifikasi wajah gagal');
            return false;
        } else {
            $responseData = $response->json();
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
}
