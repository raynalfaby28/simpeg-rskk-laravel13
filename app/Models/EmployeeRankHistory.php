<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeRankHistory extends Model
{
    protected $table = 'employee_rank_histories';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_sk' => 'date',
        'tmt' => 'date',
        'masa_kerja_tahun' => 'integer',
        'masa_kerja_bulan' => 'integer',
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