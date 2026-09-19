<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryHistory extends Model
{
    protected $table = 'employee_salary_histories';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_sk' => 'date',
        'tmt' => 'date',
        'masa_kerja_tahun' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }
}