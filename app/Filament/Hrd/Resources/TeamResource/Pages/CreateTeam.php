<?php

namespace App\Filament\Hrd\Resources\TeamResource\Pages;

use App\Filament\Hrd\Resources\TeamResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Ambil data anggota tim
        $teamMembers = $data['team_members'] ?? [];
        
        // Hapus team_members dari data utama
        unset($data['team_members']);
        
        // Buat tim
        $team = static::getModel()::create($data);
        
        // Attach anggota tim
        if (!empty($teamMembers)) {
            $team->members()->attach($teamMembers);
        }
        
        return $team;
    }
}