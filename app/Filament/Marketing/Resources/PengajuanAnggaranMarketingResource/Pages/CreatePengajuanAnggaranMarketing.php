<?php

namespace App\Filament\Marketing\Resources\PengajuanAnggaranMarketingResource\Pages;

use App\Filament\Marketing\Resources\PengajuanAnggaranMarketingResource;
use App\Models\Team;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreatePengajuanAnggaranMarketing extends CreateRecord
{
    protected static string $resource = PengajuanAnggaranMarketingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $detailsData = $data['details'] ?? [];
        $attachmentsData = $data['attachments'] ?? [];
        
        $total = 0;
        $marketingTeamId = Team::where('name', 'Marketing')->firstOrFail()->id; 

        foreach ($detailsData as &$detail) {
            $total += (float) ($detail['amount'] ?? 0);
            $detail['team_id'] = $marketingTeamId;
        }

        $data['total_amount'] = $total;
        $data['type'] = 'expense';
        $data['status'] = 'pending';
        $data['user_id'] = Auth::id();

        unset($data['details'], $data['attachments']);

        $transaction = static::getModel()::create($data);

        if (!empty($detailsData)) {
            $transaction->details()->createMany($detailsData);
        }
        if (!empty($attachmentsData)) {
            $transaction->attachments()->createMany($attachmentsData);
        }

        return $transaction;
    }
}