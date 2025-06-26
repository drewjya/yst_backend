<?php
namespace App\Filament\Resources\HappyHourResource\Pages;

use App\Filament\Resources\HappyHourResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHappyHour extends ViewRecord
{
    protected static string $resource = HappyHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

}
