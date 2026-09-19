<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePppkContract extends Model
{
    protected $table = 'employee_pppk_contracts';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}