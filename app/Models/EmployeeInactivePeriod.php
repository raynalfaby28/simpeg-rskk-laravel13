<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeInactivePeriod extends Model
{
    protected $table = 'employee_inactive_periods';

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