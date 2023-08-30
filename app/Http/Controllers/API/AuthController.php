<?php

namespace App\Http\Controllers\API;

use Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\SendTempPasswordMail;
use Illuminate\Support\Facades\DB;
use App\Models\PasswordResetTokens;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestResetPasswordMail;
use App\Http\Resources\StudentResource;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        $credentials = $request->only('email', 'password');
        $token = Auth::guard('api')->attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 200);
        }
        $user = Auth::guard('api')->user();
        return response()->json([
            'status' => 'success',
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'isDefault' => $user->isDefault
            ],
            'profile' => $user->role == 'student' ? $user->student : $user->teacher,
            'auth' => [
                'token' => $token,
                'type' => 'bearer'
            ]
        ], 200);
    }

    public function changePassword(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'password' => 'required|string|confirmed'
            ]);
            $user = User::where('id', Auth::guard('api')->user()->id)->update([
                'password' => Hash::make($request->password),
                'isDefault' => false
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Password berhasil diubah'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function requestResetPassword(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);
            $token = Str::random(32);

            $resetToken = PasswordResetTokens::where('email', $request->email)->first();
            if ($resetToken) {
                $resetToken = PasswordResetTokens::where('email', $request->email)->update([
                    'token' => $token
                ]);
            } else {
                PasswordResetTokens::create(['email' => $request->email, 'token' => $token]);
            }
            // $resetLink = route('reset-password', ['token' => $token]);

            Mail::to($request->email)->send(new RequestResetPasswordMail($token));
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Link Reset Password Berhasil Dikirimkan Ke Email Anda'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
