<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAward extends Model
{
    protected $table = 'employee_awards';

    protected $guarded = ['id'];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}