<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiklatType extends Model
{
    protected $table = 'diklat_types';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}