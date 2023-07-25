<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Resources\LaporanPresensiResource;

class PresenceController extends Controller
{
    public function index()
    {
        $data = Student::whereHas('presencesToday')->orWhereHas('absencesToday')->latest()->get();
    
        return view('pages.presence.index', compact('data'));
    }
}
