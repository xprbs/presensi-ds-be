<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;

class PresensiExport implements FromView
{
    public $data;
    public $category;

    public function __construct($data, $category)
    {
        $this->data = $data;
        $this->category = $category;
    }

    public function view(): View
    {
        // dd($this->category);
        return view('exports.presensi', [
            'datas' => $this->data,
            'memew' => $this->category
        ]);
    }
}
