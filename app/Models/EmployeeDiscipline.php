<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDiscipline extends Model
{
    protected $table = 'employee_disciplines';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}