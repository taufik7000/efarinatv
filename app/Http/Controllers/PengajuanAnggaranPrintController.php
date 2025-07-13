<?php

namespace App\Http\Controllers;

use App\Models\FinanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanAnggaranPrintController extends Controller
{
    /**
     * Print/Export pengajuan anggaran
     */
    public function print(FinanceTransaction $pengajuan)
    {
        // Pastikan user hanya bisa print pengajuan mereka sendiri
        if ($pengajuan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        // Load relasi yang diperlukan
        $pengajuan->load(['details.category', 'details.team', 'attachments.uploadedBy', 'user']);

        return view('pengajuan-anggaran.print', compact('pengajuan'));
    }

    /**
     * Export pengajuan anggaran ke PDF
     */
    public function exportPdf(FinanceTransaction $pengajuan)
    {
        // Pastikan user hanya bisa export pengajuan mereka sendiri
        if ($pengajuan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        // Load relasi yang diperlukan
        $pengajuan->load(['details.category', 'details.team', 'attachments.uploadedBy', 'user']);

        // Jika menggunakan DomPDF atau library PDF lainnya
        // $pdf = PDF::loadView('pengajuan-anggaran.pdf', compact('pengajuan'));
        // return $pdf->download('pengajuan-anggaran-' . $pengajuan->id . '.pdf');

        // Untuk sekarang, return view biasa yang bisa di-print browser
        return view('pengajuan-anggaran.print', compact('pengajuan'))
            ->with('isPdf', true);
    }
}