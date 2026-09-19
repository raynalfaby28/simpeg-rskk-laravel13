<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePmkHistory extends Model
{
    protected $table = 'employee_pmk_histories';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_sk' => 'date',
        'tmt' => 'date',
        'tambah_tahun' => 'integer',
        'tambah_bulan' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}