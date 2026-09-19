<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFamily extends Model
{
    protected $table = 'employee_families';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'status_tanggungan' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}