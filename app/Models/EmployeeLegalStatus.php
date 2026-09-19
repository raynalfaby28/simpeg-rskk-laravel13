<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLegalStatus extends Model
{
    protected $table = 'employee_legal_statuses';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}