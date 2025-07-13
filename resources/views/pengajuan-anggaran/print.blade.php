<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Anggaran - {{ $pengajuan->project_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }
        
        .title {
            font-size: 20px;
            margin: 10px 0;
            color: #1f2937;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-box {
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9fafb;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #6b7280;
        }
        
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status.pending { background-color: #fef3c7; color: #92400e; }
        .status.approved { background-color: #d1fae5; color: #065f46; }
        .status.rejected { background-color: #fee2e2; color: #991b1b; }
        
        .urgency {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .urgency.low { background-color: #e5e7eb; color: #374151; }
        .urgency.medium { background-color: #bfdbfe; color: #1e40af; }
        .urgency.high { background-color: #fed7aa; color: #ea580c; }
        .urgency.urgent { background-color: #fecaca; color: #dc2626; }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        
        .table tr:hover {
            background-color: #f9fafb;
        }
        
        .total-box {
            background-color: #ecfdf5;
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #059669;
        }
        
        .print-info {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .no-print {
                display: none;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
        
        .attachment-list {
            list-style: none;
            padding: 0;
        }
        
        .attachment-item {
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            margin-bottom: 8px;
            background-color: #f9fafb;
        }
        
        .description-text {
            line-height: 1.7;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">Efarina TV</div>
        <div class="title">FORMULIR PENGAJUAN ANGGARAN</div>
        <div style="font-size: 14px; color: #6b7280;">
            Nomor: PGA/{{ str_pad($pengajuan->id, 4, '0', STR_PAD_LEFT) }}/{{ $pengajuan->created_at->format('m/Y') }}
        </div>
    </div>

    <!-- Informasi Dasar -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Status Pengajuan</div>
            <div class="info-value">
                <span class="status {{ $pengajuan->status }}">
                    {{ $pengajuan->status_text }}
                </span>
            </div>
        </div>
        
        <div class="info-box">
            <div class="info-label">Tingkat Urgensi</div>
            <div class="info-value">
                <span class="urgency {{ $pengajuan->urgency_level }}">
                    {{ $pengajuan->urgency_text }}
                </span>
                @if($pengajuan->is_urgent_request)
                    <span style="color: #dc2626; font-weight: bold;">⚠️ URGENT REQUEST</span>
                @endif
            </div>
        </div>
        
        <div class="info-box">
            <div class="info-label">Tanggal Pengajuan</div>
            <div class="info-value">{{ $pengajuan->created_at->format('d F Y, H:i') }}</div>
        </div>
        
        <div class="info-box">
            <div class="info-label">Tanggal Dibutuhkan</div>
            <div class="info-value">
                {{ $pengajuan->transaction_date->format('d F Y') }}
                @if($pengajuan->is_overdue)
                    <span style="color: #dc2626; font-weight: bold;">(TERLAMBAT)</span>
                @endif
            </div>
        </div>
        
        <div class="info-box">
            <div class="info-label">Diajukan Oleh</div>
            <div class="info-value">{{ $pengajuan->user->name }}</div>
        </div>
        
        <div class="info-box">
            <div class="info-label">Jenis Anggaran</div>
            <div class="info-value">{{ $pengajuan->budget_type_text }}</div>
        </div>
    </div>

    <!-- Detail Proyek -->
    <div class="section">
        <div class="section-title">📋 Detail Proyek/Kegiatan</div>
        
        <div class="info-box">
            <div class="info-label">Nama Proyek/Kegiatan</div>
            <div class="info-value" style="font-size: 16px; font-weight: bold; color: #1f2937;">
                {{ $pengajuan->project_name }}
            </div>
        </div>
        
        <div style="margin-top: 15px;">
            <div class="info-label">Deskripsi & Tujuan</div>
            <div class="description-text">{{ $pengajuan->description }}</div>
        </div>
        
        @if($pengajuan->expected_completion)
        <div style="margin-top: 15px;">
            <div class="info-label">Estimasi Penyelesaian</div>
            <div class="info-value">{{ $pengajuan->expected_completion }}</div>
        </div>
        @endif
        
        @if($pengajuan->pic_contact)
        <div style="margin-top: 15px;">
            <div class="info-label">PIC/Contact Person</div>
            <div class="info-value">{{ $pengajuan->pic_contact }}</div>
        </div>
        @endif
    </div>

    <!-- Total Anggaran -->
    <div class="total-box">
        <div style="font-size: 14px; color: #6b7280; margin-bottom: 5px;">TOTAL ANGGARAN YANG DIAJUKAN</div>
        <div class="total-amount">{{ $pengajuan->formatted_total_amount }}</div>
        <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">
            ({{ $pengajuan->total_items }} item{{ $pengajuan->total_items > 1 ? 's' : '' }})
        </div>
    </div>

    <!-- Rincian Anggaran -->
    <div class="section">
        <div class="section-title">💰 Rincian Anggaran</div>
        
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Deskripsi Item</th>
                    <th style="width: 20%;">Kategori</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 15%;">Harga Satuan</th>
                    <th style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengajuan->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $detail->item_description }}</strong>
                        @if($detail->justification)
                            <br><small style="color: #6b7280;">{{ $detail->justification }}</small>
                        @endif
                        @if($detail->supplier_vendor)
                            <br><small style="color: #059669;">Supplier: {{ $detail->supplier_vendor_text }}</small>
                        @endif
                    </td>
                    <td>{{ $detail->category->name ?? 'N/A' }}</td>
                    <td>{{ $detail->quantity ?? 1 }}</td>
                    <td>{{ $detail->formatted_unit_price }}</td>
                    <td><strong>{{ $detail->formatted_amount }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f3f4f6; font-weight: bold;">
                    <td colspan="5" style="text-align: right;">TOTAL KESELURUHAN:</td>
                    <td style="color: #059669; font-size: 16px;">{{ $pengajuan->formatted_total_amount }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Informasi Urgent -->
    @if($pengajuan->is_urgent_request && $pengajuan->urgent_reason)
    <div class="section">
        <div class="section-title" style="color: #dc2626;">⚠️ Alasan Urgent</div>
        <div class="info-box" style="border-color: #fca5a5; background-color: #fef2f2;">
            <div class="description-text">{{ $pengajuan->urgent_reason }}</div>
        </div>
    </div>
    @endif

    <!-- Catatan Tambahan -->
    @if($pengajuan->additional_notes)
    <div class="section">
        <div class="section-title">📝 Catatan Tambahan</div>
        <div class="description-text">{{ $pengajuan->additional_notes }}</div>
    </div>
    @endif

    <!-- Dokumen Pendukung -->
    @if($pengajuan->attachments->isNotEmpty())
    <div class="section">
        <div class="section-title">📎 Dokumen Pendukung</div>
        <ul class="attachment-list">
            @foreach($pengajuan->attachments as $attachment)
            <li class="attachment-item">
                <strong>{{ $attachment->file_name }}</strong>
                <span style="color: #6b7280;">({{ $attachment->formatted_file_size }})</span>
                <br>
                <small>{{ $attachment->document_type_text }}</small>
                @if($attachment->document_description)
                    <br><small style="color: #6b7280;">{{ $attachment->document_description }}</small>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Informasi Persetujuan -->
    <div class="section">
        <div class="section-title">✅ Informasi Persetujuan</div>
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">Persetujuan Diperlukan Oleh</div>
                <div class="info-value">{{ $pengajuan->approval_needed_by_text }}</div>
            </div>
            @if($pengajuan->approved_at)
            <div class="info-box">
                <div class="info-label">Disetujui Pada</div>
                <div class="info-value">{{ $pengajuan->approved_at->format('d F Y, H:i') }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Print Info -->
    <div class="print-info">
        <div>Dokumen ini dicetak pada {{ now()->format('d F Y, H:i') }}</div>
        <div>Efarina TV - Sistem Manajemen Pengajuan Anggaran</div>
    </div>

    <!-- Print Button (hanya tampil di screen) -->
    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" 
                style="background-color: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px;">
            🖨️ Print Dokumen
        </button>
        <button onclick="window.close()" 
                style="background-color: #6b7280; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin-left: 10px;">
            ❌ Tutup
        </button>
    </div>
</body>
</html>