<?php
namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Absence;
use App\Models\Presence;

class PresensiValidation
{
    public static function validatePresensiMasuk($nipd)
    {
        $isPresensiExists = Presence::where('nipd', $nipd)->whereDate('presence_in', today())->first();
        if ($isPresensiExists) {
            $errCode = 222;
            return $errCode;
        }

        $isAbsenceExists = Absence::where('nipd', $nipd)->whereDate('absence_date', today())->whereIn('absence_type', ['sakit', 'izin'])->first();
        if ($isAbsenceExists) {
            $errCode = 223;
            return $errCode;
        }

        $isAbsenceWithoutNotes = Absence::where('nipd', $nipd)->whereDate('absence_date', today())->where('absence_type', 'alfa')->first();
        if ($isAbsenceExists) {
            $errCode = 224;
            return $errCode;
        }
    }

    public static function scheduleMasukValidator($type)
    {
        $now = Carbon::now();
        if ($type === 'pagi') {
            $batasAwal = Carbon::createFromTime(6, 45, 0);
            $batasAkhir = Carbon::createFromTime(7, 0, 0);
        } elseif ($type === 'siang') {
            $batasAwal = Carbon::createFromTime(12, 45, 0);
            $batasAkhir = Carbon::createFromTime(13, 0, 0);
        } else {
            return false;
        }

        if ($now < $batasAwal) {
            // Belum waktunya masuk kelas
            $msg = 'Minimal 15 menit sebelum pukul ' . $batasAwal->format('H:i');
            return $msg;
        } elseif ($now <= $batasAkhir) {
            // Masuk kelas tepat waktu
            $msg = '';
            return $msg;
        } else {
            // Terlambat masuk kelas
            $telatMenit = $now->diffInMinutes($batasAkhir);
            $msg = 'Telat ' . $telatMenit . ' menit';
            return $msg;
        }
    }

    public static function validatePresensiKeluar($nipd)
    {
        $isPresensiExists = Presence::where('nipd', $nipd)->whereDate('created_at', today())->first();
        if (!$isPresensiExists) {
            return false;
        }
        return true;
    }
}


