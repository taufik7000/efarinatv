<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    use HasFactory;

    /**
     * Atribut yang tidak boleh diisi secara massal.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Cast attributes
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Mendefinisikan relasi "one-to-many" ke model FinanceBudget.
     * Sebuah tim bisa memiliki banyak alokasi anggaran.
     */
    public function financeBudgets(): HasMany
    {
        return $this->hasMany(FinanceBudget::class);
    }

    /**
     * Mendefinisikan relasi "one-to-many" ke model FinanceTransactionDetail.
     * Sebuah tim bisa memiliki banyak rincian transaksi.
     */
    public function financeTransactionDetails(): HasMany
    {
        return $this->hasMany(FinanceTransactionDetail::class);
    }

    /**
     * Relasi many-to-many dengan User (anggota tim)
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Relasi ke ketua tim
     */
    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    /**
     * Relasi ke tasks yang terkait dengan tim ini
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_team_id');
    }

    /**
     * Scope untuk tim yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk tim berdasarkan departemen
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Accessor untuk mendapatkan semua anggota termasuk ketua
     */
    public function getAllMembersAttribute()
    {
        $members = $this->members;
        if ($this->teamLeader && !$members->contains($this->teamLeader)) {
            $members->push($this->teamLeader);
        }
        return $members;
    }

    /**
     * Accessor untuk mendapatkan total anggota
     */
    public function getTotalMembersAttribute(): int
    {
        return $this->members()->count();
    }

    /**
     * Accessor untuk status aktif yang readable
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    /**
     * Accessor untuk departemen yang formatted
     */
    public function getDepartmentTextAttribute(): string
    {
        return match($this->department) {
            'redaksi' => 'Redaksi',
            'marketing' => 'Marketing',
            'keuangan' => 'Keuangan',
            'hrd' => 'HRD',
            'teknis' => 'Teknis',
            'administrasi' => 'Administrasi',
            default => ucfirst($this->department ?? 'Umum')
        };
    }

    /**
     * Method untuk menambah anggota tim
     */
    public function addMember(User $user): void
    {
        if (!$this->members()->where('user_id', $user->id)->exists()) {
            $this->members()->attach($user->id);
        }
    }

    /**
     * Method untuk menghapus anggota tim
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
        
        // Jika yang dihapus adalah ketua tim, set ketua menjadi null
        if ($this->team_leader_id === $user->id) {
            $this->update(['team_leader_id' => null]);
        }
    }

    /**
     * Method untuk mengecek apakah user adalah anggota tim
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists() || 
               $this->team_leader_id === $user->id;
    }

    /**
     * Method untuk mengecek apakah user adalah ketua tim
     */
    public function isLeader(User $user): bool
    {
        return $this->team_leader_id === $user->id;
    }

    /**
     * Boot method untuk handle events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($team) {
            // Detach semua anggota sebelum menghapus tim
            $team->members()->detach();
        });
    }
}