<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class StudentPresentSemesterExport implements FromView
{
    protected $data;
    protected $studentData;
    protected $totals;
    protected $semester;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($data, $studentData, $totals, $semester)
    {
        $this->data = $data;
        $this->studentData = $studentData;
        $this->totals = $totals;
        $this->semester = $semester;
    }

    public function view(): View
    {
        // dd($this->studentData);
        return view('exports.student-presensi-semester', [
            'data' => $this->data,
            'studentData' => $this->studentData,
            'totals' => $this->totals,
            'semester' => $this->semester
        ]);
    }


}
