<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FinanceTransactionDetail extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'finance_transaction_details';

    /**
     * Atribut yang tidak boleh diisi secara massal untuk melindungi dari mass assignment.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Default values for attributes
     */
    protected $attributes = [
        'quantity' => 1,
    ];

    // --- RELATIONS ---

    /**
     * Mendefinisikan relasi "belongsTo" ke model FinanceTransaction.
     * Setiap rincian transaksi dimiliki oleh satu transaksi induk.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'transaction_id');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Team.
     * Setiap rincian transaksi dibebankan ke satu tim.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model FinanceCategory.
     * Setiap rincian transaksi masuk ke dalam satu kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }

    // --- SCOPES ---

    /**
     * Scope untuk filter berdasarkan kategori
     */
    public function scopeByCategory(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }

    /**
     * Scope untuk filter berdasarkan tim
     */
    public function scopeByTeam(Builder $query, int $teamId): void
    {
        $query->where('team_id', $teamId);
    }

    /**
     * Scope untuk filter berdasarkan supplier/vendor
     */
    public function scopeBySupplier(Builder $query, string $supplier): void
    {
        $query->where('supplier_vendor', $supplier);
    }

    /**
     * Scope untuk detail dengan nilai tinggi
     */
    public function scopeHighValue(Builder $query, float $minAmount = 1000000): void
    {
        $query->where('amount', '>=', $minAmount);
    }

    // --- ACCESSORS ---

    /**
     * Accessor untuk mendapatkan total amount yang diformat
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Accessor untuk mendapatkan unit price yang diformat
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price ?? 0, 0, ',', '.');
    }

    /**
     * Accessor untuk mendapatkan supplier/vendor text yang lebih readable
     */
    public function getSupplierVendorTextAttribute(): string
    {
        return match ($this->supplier_vendor) {
            'toko_a' => 'Toko A - Elektronik',
            'vendor_b' => 'Vendor B - Catering',
            'supplier_c' => 'Supplier C - ATK',
            'other' => 'Lainnya',
            default => $this->supplier_vendor ?? 'Tidak Ditentukan',
        };
    }

    /**
     * Accessor untuk mendapatkan deskripsi item yang disingkat
     */
    public function getShortDescriptionAttribute(): string
    {
        if (!$this->item_description) {
            return 'Item tidak ada deskripsi';
        }
        
        return strlen($this->item_description) > 50 
            ? substr($this->item_description, 0, 50) . '...'
            : $this->item_description;
    }

    /**
     * Accessor untuk mendapatkan informasi quantity dan unit
     */
    public function getQuantityInfoAttribute(): string
    {
        $qty = $this->quantity ?? 1;
        $unit = $this->unit ?? 'unit';
        return "{$qty} {$unit}";
    }

    // --- MUTATORS ---

    /**
     * Mutator untuk item_description (capitalize first letter)
     */
    public function setItemDescriptionAttribute($value): void
    {
        $this->attributes['item_description'] = $value ? ucfirst(trim($value)) : null;
    }

    /**
     * Mutator untuk justification (clean up text)
     */
    public function setJustificationAttribute($value): void
    {
        $this->attributes['justification'] = $value ? trim($value) : null;
    }

    /**
     * Mutator untuk amount - auto calculate dari quantity dan unit_price jika kosong
     */
    public function setAmountAttribute($value): void
    {
        if (!$value && $this->quantity && $this->unit_price) {
            $this->attributes['amount'] = $this->quantity * $this->unit_price;
        } else {
            $this->attributes['amount'] = $value;
        }
    }

    // --- METHODS ---

    /**
     * Hitung ulang amount berdasarkan quantity dan unit_price
     */
    public function recalculateAmount(): void
    {
        if ($this->quantity && $this->unit_price) {
            $this->update([
                'amount' => $this->quantity * $this->unit_price
            ]);
        }
    }

    /**
     * Check apakah item ini termasuk kategori high value
     */
    public function isHighValue(float $threshold = 1000000): bool
    {
        return $this->amount >= $threshold;
    }

    /**
     * Get summary info untuk item ini
     */
    public function getSummaryInfo(): array
    {
        return [
            'description' => $this->short_description,
            'quantity' => $this->quantity ?? 1,
            'unit_price' => $this->formatted_unit_price,
            'total' => $this->formatted_amount,
            'category' => $this->category?->name ?? 'Tidak Ada Kategori',
            'team' => $this->team?->name ?? 'Tidak Ada Tim',
            'supplier' => $this->supplier_vendor_text,
        ];
    }

    // --- BOOT METHOD ---
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate amount saat creating/updating
        static::saving(function ($detail) {
            // Jika amount kosong tapi ada quantity dan unit_price, hitung otomatis
            if (!$detail->amount && $detail->quantity && $detail->unit_price) {
                $detail->amount = $detail->quantity * $detail->unit_price;
            }
        });

        // Update total di transaction parent saat detail berubah
        static::saved(function ($detail) {
            if ($detail->transaction) {
                $detail->transaction->recalculateTotal();
            }
        });

        static::deleted(function ($detail) {
            if ($detail->transaction) {
                $detail->transaction->recalculateTotal();
            }
        });
    }
}