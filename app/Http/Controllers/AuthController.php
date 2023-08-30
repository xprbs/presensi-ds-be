<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\SendTempPasswordMail;
use Illuminate\Support\Facades\DB;
use App\Models\PasswordResetTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('index');
        } else {
            return view('pages.auth.login');
        }
    }
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
 
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
 
            return redirect()->route('index');
        }
 
        return back()->withErrors([
            'error' => 'Email atau password salah',
        ])->onlyInput('email');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function verifyToken($token)
    {
        DB::beginTransaction();
        try {
            $isTokenExists = PasswordResetTokens::where('token', $token)->first();
            $status = true;

            $generatePassword = Str::random(16);
            $changeUserPassword = User::where('email', $isTokenExists->email)->update([
                'password' => Hash::make($generatePassword),
                'isDefault' => true
            ]);

            Mail::to($isTokenExists->email)->send(new SendTempPasswordMail($generatePassword));
            $deleteToken = PasswordResetTokens::where('token', $token)->delete();
            DB::commit();
            return view('pages.emails.verify_reset_token', compact('status'));
        } catch (\Exception $e) {
            $status = false;
            $msg = $e->getMessage();
            DB::rollback();
            return view('pages.emails.verify_reset_token', compact('status', 'msg'));
        }
    }
}
