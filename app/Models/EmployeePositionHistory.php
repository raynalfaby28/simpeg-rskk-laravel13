<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePositionHistory extends Model
{
    protected $table = 'employee_position_histories';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_sk' => 'date',
        'tmt' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function workUnit()
    {
        return $this->belongsTo(WorkUnit::class, 'work_unit_id');
    }
}