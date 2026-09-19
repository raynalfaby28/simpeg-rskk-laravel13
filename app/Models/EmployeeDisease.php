<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDisease extends Model
{
    protected $table = 'employee_diseases';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}