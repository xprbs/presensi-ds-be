<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OnlinePresenceController extends Controller
{
    public function index(Request $request)
    {
        try {
            //get source path from frontend
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
            $verified = isset($responseData['verified']) ? $responseData['verified'] : false;

            //Delete temp file
            Storage::delete('public/temp/'.$filename);
            return response()->json([
                'status' => 'success',
                'verified' => $verified,
                'source' => $sourceUrl,
                'source2' => $datasetUrl,
                'url' => $url
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
