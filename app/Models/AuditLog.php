<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $guarded = ['id'];

    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public static function record(string $action, string $module, $reference = null, ?string $description = null): ?self
    {
        if (! auth()->check()) {
            return null;
        }

        $user = auth()->user();

        $employeeId = null;
        if ($reference instanceof \App\Models\Employee) {
            $employeeId = $reference->id;
        } elseif ($reference) {
            $raw = $reference->getAttribute('employee_id');
            $employeeId = filled($raw) ? $raw : null;
            if (! $employeeId && $reference instanceof User && $reference->employee) {
                $employeeId = $reference->employee->id;
            }
        }

        return static::create([
            'user_id' => $user->id,
            'user_name' => trim($user->name . ' (' . $user->nip . ')'),
            'employee_id' => $employeeId,
            'action' => $action,
            'module' => $module,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->getKey() : null,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}