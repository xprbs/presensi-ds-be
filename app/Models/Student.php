<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory;
    protected $table = 'students';
    protected $primaryKey = 'nipd';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nipd',
        'user_id',
        'class_id',
        'name',
        'gender',
        'pob',
        'dob',
        'religion',
        'address',
        'residence_type',
        'photo'
    ];

    public function classroom()
    {
        return $this->hasOne(Classroom::class, 'id', 'class_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function presences()
    {
        return $this->hasMany(Presence::class, 'nipd', 'nipd');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'nipd', 'nipd');
    }

    public function presencesToday()
    {
        return $this->hasOne(Presence::class, 'nipd', 'nipd')->whereNotNull('presence_in')->whereDate('presence_in', today());
    }

    public function absencesToday()
    {
        return $this->hasOne(Absence::class, 'nipd', 'nipd')->whereNotNull('absence_date')->whereDate('absence_date', today());
    }

    public function getPresencesTodayCountAttribute()
    {
        return $this->presencesToday()->count();
    }

    public function getAbsencesTodayCountAttribute()
    {
        return $this->absencesToday()->count();
    }

    // Eloquent Accessor untuk menghitung jumlah izin
    public function getIzinTodayCountAttribute()
    {
        return $this->absencesToday()->where('absence_type', 'izin')->count();
    }

    // Eloquent Accessor untuk menghitung jumlah sakit
    public function getSakitTodayCountAttribute()
    {
        return $this->absencesToday()->where('absence_type', 'sakit')->count();
    }

    // Eloquent Accessor untuk menghitung jumlah alfa
    public function getAlfaTodayCountAttribute()
    {
        return $this->absencesToday()->where('absence_type', 'alfa')->count();
    }

    public function presencesOnDate()
    {
        return $this->hasOne(Presence::class, 'nipd', 'nipd')->whereNotNull('presence_in');
    }

    public function absencesOnDate()
    {
        return $this->hasOne(Absence::class, 'nipd', 'nipd')->whereNotNull('absence_date');
    }

    public function presenceSemester()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfDay();
        return $this->presences()->whereBetween('presence_in', [$sixMonthsAgo, Carbon::now()]);
    }
}
