<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCreditScore extends Model
{
    protected $table = 'employee_credit_scores';

    protected $guarded = ['id'];

    protected $casts = [
        'tahun' => 'integer',
        'nilai_angka_kredit' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}