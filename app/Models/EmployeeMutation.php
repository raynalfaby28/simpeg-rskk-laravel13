<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeMutation extends Model
{
    protected $table = 'employee_mutations';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mutasi' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function unitAsal()
    {
        return $this->belongsTo(WorkUnit::class, 'unit_asal_id');
    }

    public function unitTujuan()
    {
        return $this->belongsTo(WorkUnit::class, 'unit_tujuan_id');
    }
}