<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformance extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tahun' => 'integer',
        'nilai' => 'decimal:2',
        'file_path' => 'string',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}