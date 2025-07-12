<?php

namespace App\Filament\Hrd\Resources\TeamResource\Pages;

use App\Filament\Hrd\Resources\TeamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTeam extends EditRecord
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load anggota tim untuk form
        $data['team_members'] = $this->record->members()->pluck('users.id')->toArray();
        
        return $data;
    }
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Ambil data anggota tim
        $teamMembers = $data['team_members'] ?? [];
        
        // Hapus team_members dari data utama
        unset($data['team_members']);
        
        // Update data tim
        $record->update($data);
        
        // Sync anggota tim
        $record->members()->sync($teamMembers);
        
        return $record;
    }
}