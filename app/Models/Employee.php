<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'kepemilikan_kpe' => 'boolean',
            'izin_pemakaian_gelar' => 'boolean',
            'tmt_eselon' => 'date',
            'tmt_jabatan' => 'date',
            'tmt_tugas_tambahan_1' => 'date',
            'tmt_tugas_tambahan_2' => 'date',
            'tmt_skpd' => 'date',
            'tmt_golongan_awal' => 'date',
            'tmt_golongan_akhir' => 'date',
            'tmt_gaji_berkala_terbaru' => 'date',
            'gaji_pokok' => 'decimal:2',
            'data_updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workUnit()
    {
        return $this->belongsTo(WorkUnit::class, 'work_unit_id');
    }

    public function currentPosition()
    {
        return $this->belongsTo(Position::class, 'current_position_id');
    }

    public function employeeCategory()
    {
        return $this->belongsTo(EmployeeCategory::class);
    }

    public function employmentStatus()
    {
        return $this->belongsTo(EmploymentStatus::class);
    }

    public function golonganAwal()
    {
        return $this->belongsTo(Rank::class, 'golongan_awal_id');
    }

    public function golonganAkhir()
    {
        return $this->belongsTo(Rank::class, 'golongan_akhir_id');
    }

    public function pendidikanAwal()
    {
        return $this->belongsTo(EducationLevel::class, 'pendidikan_awal_id');
    }

    public function pendidikanAkhir()
    {
        return $this->belongsTo(EducationLevel::class, 'pendidikan_akhir_id');
    }

    // Riwayat
    public function educations()
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function positionHistories()
    {
        return $this->hasMany(EmployeePositionHistory::class);
    }

    public function rankHistories()
    {
        return $this->hasMany(EmployeeRankHistory::class);
    }

    public function salaryHistories()
    {
        return $this->hasMany(EmployeeSalaryHistory::class);
    }

    public function mutations()
    {
        return $this->hasMany(EmployeeMutation::class);
    }

    public function trainings()
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    public function awards()
    {
        return $this->hasMany(EmployeeAward::class);
    }

    public function disciplines()
    {
        return $this->hasMany(EmployeeDiscipline::class);
    }

    public function performances()
    {
        return $this->hasMany(EmployeePerformance::class);
    }

    public function families()
    {
        return $this->hasMany(EmployeeFamily::class);
    }

    public function languages()
    {
        return $this->hasMany(EmployeeLanguage::class);
    }

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function inactivePeriods()
    {
        return $this->hasMany(EmployeeInactivePeriod::class);
    }

    public function legalStatuses()
    {
        return $this->hasMany(EmployeeLegalStatus::class);
    }

    public function diseases()
    {
        return $this->hasMany(EmployeeDisease::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function pmkHistories()
    {
        return $this->hasMany(EmployeePmkHistory::class);
    }

    public function pppkContracts()
    {
        return $this->hasMany(EmployeePppkContract::class);
    }

    public function skps()
    {
        return $this->hasMany(EmployeeSkp::class);
    }

    public function creditScores()
    {
        return $this->hasMany(EmployeeCreditScore::class);
    }

    public function ipasns()
    {
        return $this->hasMany(EmployeeIpasn::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function assets()
    {
        return $this->hasMany(EmployeeAsset::class);
    }

    public function changeRequests()
    {
        return $this->hasMany(ChangeRequest::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'employee_id');
    }

    public function getNamaLengkapDenganGelarAttribute(): string
    {
        return trim("{$this->gelar_depan} {$this->nama_lengkap}, {$this->gelar_belakang}");
    }
}
