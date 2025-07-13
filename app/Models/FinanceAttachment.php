<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class FinanceAttachment extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'finance_attachments';

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
        'file_size' => 'integer',
    ];

    /**
     * Default values for attributes
     */
    protected $attributes = [
        'document_type' => 'other',
    ];

    // --- RELATIONS ---

    /**
     * Mendefinisikan relasi "belongsTo" ke model FinanceTransaction.
     * Setiap lampiran dimiliki oleh satu transaksi induk.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'transaction_id');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model User.
     * Menunjukkan siapa yang mengunggah file ini.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // --- SCOPES ---

    /**
     * Scope untuk filter berdasarkan jenis dokumen
     */
    public function scopeByDocumentType(Builder $query, string $type): void
    {
        $query->where('document_type', $type);
    }

    /**
     * Scope untuk filter berdasarkan user yang upload
     */
    public function scopeByUploader(Builder $query, int $userId): void
    {
        $query->where('uploaded_by', $userId);
    }

    /**
     * Scope untuk file dengan ukuran besar
     */
    public function scopeLargeFiles(Builder $query, int $minSize = 5242880): void // 5MB default
    {
        $query->where('file_size', '>=', $minSize);
    }

    /**
     * Scope untuk file berdasarkan extension
     */
    public function scopeByFileType(Builder $query, string $extension): void
    {
        $query->where('file_type', 'like', "%{$extension}%");
    }

    /**
     * Scope untuk file PDF
     */
    public function scopePdfFiles(Builder $query): void
    {
        $query->where('file_type', 'like', '%pdf%');
    }

    /**
     * Scope untuk file gambar
     */
    public function scopeImageFiles(Builder $query): void
    {
        $query->whereIn('file_type', [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'
        ]);
    }

    /**
     * Scope untuk file Excel/Word
     */
    public function scopeOfficeFiles(Builder $query): void
    {
        $query->where(function ($q) {
            $q->where('file_type', 'like', '%excel%')
              ->orWhere('file_type', 'like', '%spreadsheet%')
              ->orWhere('file_type', 'like', '%word%')
              ->orWhere('file_type', 'like', '%document%');
        });
    }

    // --- ACCESSORS ---

    /**
     * Accessor untuk mendapatkan ukuran file yang diformat
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Ukuran tidak diketahui';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Accessor untuk mendapatkan document type yang lebih readable
     */
    public function getDocumentTypeTextAttribute(): string
    {
        return match ($this->document_type) {
            'quotation' => 'Quotation/Penawaran Harga',
            'specification' => 'Spesifikasi Teknis',
            'comparison' => 'Perbandingan Harga',
            'proposal' => 'Proposal Kegiatan',
            'reference' => 'Referensi/Contoh',
            'invoice' => 'Invoice/Tagihan',
            'receipt' => 'Kwitansi/Bukti Bayar',
            'contract' => 'Kontrak/Perjanjian',
            'other' => 'Lainnya',
            default => 'Tidak Ditentukan',
        };
    }

    /**
     * Accessor untuk mendapatkan file extension
     */
    public function getFileExtensionAttribute(): string
    {
        if (!$this->file_name) {
            return 'unknown';
        }
        
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }

    /**
     * Accessor untuk mendapatkan full URL file
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        
        return Storage::url($this->file_path);
    }

    /**
     * Accessor untuk check apakah file masih ada di storage
     */
    public function getFileExistsAttribute(): bool
    {
        if (!$this->file_path) {
            return false;
        }
        
        return Storage::exists($this->file_path);
    }

    /**
     * Accessor untuk mendapatkan icon berdasarkan file type
     */
    public function getFileIconAttribute(): string
    {
        $extension = $this->file_extension;
        
        return match ($extension) {
            'pdf' => 'heroicon-o-document-text',
            'doc', 'docx' => 'heroicon-o-document',
            'xls', 'xlsx' => 'heroicon-o-table-cells',
            'ppt', 'pptx' => 'heroicon-o-presentation-chart-bar',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'heroicon-o-photo',
            'zip', 'rar', '7z' => 'heroicon-o-archive-box',
            'mp4', 'avi', 'mov' => 'heroicon-o-video-camera',
            'mp3', 'wav', 'ogg' => 'heroicon-o-musical-note',
            default => 'heroicon-o-document',
        };
    }

    /**
     * Accessor untuk mendapatkan warna badge berdasarkan document type
     */
    public function getDocumentTypeColorAttribute(): string
    {
        return match ($this->document_type) {
            'quotation' => 'success',
            'specification' => 'info',
            'comparison' => 'warning',
            'proposal' => 'primary',
            'reference' => 'secondary',
            'invoice' => 'danger',
            'receipt' => 'success',
            'contract' => 'primary',
            'other' => 'gray',
            default => 'gray',
        };
    }

    // --- MUTATORS ---

    /**
     * Mutator untuk document_description
     */
    public function setDocumentDescriptionAttribute($value): void
    {
        $this->attributes['document_description'] = $value ? trim($value) : null;
    }

    /**
     * Mutator untuk file_name (clean filename)
     */
    public function setFileNameAttribute($value): void
    {
        $this->attributes['file_name'] = $value ? basename($value) : null;
    }

    // --- METHODS ---

    /**
     * Check apakah file ini adalah gambar
     */
    public function isImage(): bool
    {
        return in_array($this->file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    /**
     * Check apakah file ini adalah PDF
     */
    public function isPdf(): bool
    {
        return $this->file_extension === 'pdf';
    }

    /**
     * Check apakah file ini adalah Office document
     */
    public function isOfficeDocument(): bool
    {
        return in_array($this->file_extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
    }

    /**
     * Check apakah file berukuran besar (>5MB)
     */
    public function isLargeFile(int $threshold = 5242880): bool
    {
        return $this->file_size > $threshold;
    }

    /**
     * Download file
     */
    public function download()
    {
        if (!$this->file_exists) {
            throw new \Exception('File tidak ditemukan');
        }
        
        return Storage::download($this->file_path, $this->file_name);
    }

    /**
     * Delete file dari storage
     */
    public function deleteFile(): bool
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }
        
        return true;
    }

    /**
     * Get file info untuk display
     */
    public function getFileInfo(): array
    {
        return [
            'name' => $this->file_name,
            'size' => $this->formatted_file_size,
            'type' => $this->document_type_text,
            'extension' => $this->file_extension,
            'icon' => $this->file_icon,
            'color' => $this->document_type_color,
            'url' => $this->file_url,
            'exists' => $this->file_exists,
            'uploaded_by' => $this->uploadedBy?->name ?? 'Unknown',
            'uploaded_at' => $this->created_at?->format('d M Y H:i'),
        ];
    }

    // --- BOOT METHOD ---
    protected static function boot()
    {
        parent::boot();

        // Auto delete file saat record dihapus
        static::deleting(function ($attachment) {
            $attachment->deleteFile();
        });

        // Validate file saat saving
        static::saving(function ($attachment) {
            // Validasi bahwa uploaded_by harus ada
            if (!$attachment->uploaded_by) {
                throw new \Exception('uploaded_by harus diisi');
            }
            
            // Auto set document description jika kosong
            if (!$attachment->document_description && $attachment->file_name) {
                $attachment->document_description = 'File: ' . $attachment->file_name;
            }
        });
    }
}