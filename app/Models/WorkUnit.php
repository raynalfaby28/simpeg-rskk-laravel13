<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkUnit extends Model
{
    protected $table = 'work_units';

    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(WorkUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(WorkUnit::class, 'parent_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'work_unit_id');
    }
}
