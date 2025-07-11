<?php

namespace App\Filament\Marketing\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\FinanceTransaction;
use App\Models\FinanceCategory;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    /**
     * Mendefinisikan aksi yang akan muncul di header halaman dasbor.
     *
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('catat_pemasukan_iklan')
                ->label('Catat Pemasukan Iklan')
                ->icon('heroicon-o-currency-dollar')
                ->modalWidth('lg')
                ->form([
                    Select::make('category_id')
                        ->label('Jenis Iklan (Pemasukan)')
                        ->options(FinanceCategory::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('total_amount')
                        ->label('Jumlah Pemasukan')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),
                    TextInput::make('client_name')
                        ->label('Nama Klien / Brand')
                        ->required(),
                    TextInput::make('invoice_number')
                        ->label('Nomor Invoice (Opsional)'),
                ])
                ->action(function (array $data) {
                    // Logika untuk membuat transaksi pemasukan baru
                    FinanceTransaction::create([
                        'type' => 'income',
                        'status' => 'paid', // Pemasukan bisa langsung dianggap lunas
                        'total_amount' => $data['total_amount'],
                        'description' => 'Pemasukan iklan dari ' . $data['client_name'] . '. Invoice: ' . ($data['invoice_number'] ?? '-'),
                        'transaction_date' => now(),
                        'user_id' => Auth::id(),
                        'approved_by' => Auth::id(), // Disetujui otomatis oleh yang menginput
                        'approved_at' => now(),
                    ]);
                })
                ->modalSubmitActionLabel('Simpan Pemasukan'),
        ];
    }
}
