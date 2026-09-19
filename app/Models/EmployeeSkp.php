<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSkp extends Model
{
    protected $table = 'employee_skp';

    protected $guarded = ['id'];

    protected $casts = [
        'tahun' => 'integer',
        'nilai' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}