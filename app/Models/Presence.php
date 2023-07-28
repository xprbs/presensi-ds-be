<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presence extends Model
{
    use HasFactory;
    protected $table = 'presences';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'nipd',
        'learning_type',
        'presence_in',
        'presence_out',
        'presence_in_note',
        'presence_out_note',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'nipd', 'nipd');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('presence_in', today());
    }


    public function scopeSemester($query)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Jika bulan saat ini antara Januari hingga Juni, berarti saat ini berada di semester 2 tahun sebelumnya
        if ($currentMonth >= 1 && $currentMonth <= 6) {
            $year_next = $currentYear + 1;
            $start_date = Carbon::create($year_next, 1, 1);
            $end_date = Carbon::create($year_next, 6, 30);
        } else {
            // Jika bulan saat ini antara Juli hingga Desember, berarti saat ini berada di semester 1
            $start_date = Carbon::create($currentYear, 7, 1);
            $end_date = Carbon::create($currentYear, 12, 30);
            
        }
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfDay();
        return $query->whereBetween('presence_in', [$start_date, $end_date]);
    }
}
