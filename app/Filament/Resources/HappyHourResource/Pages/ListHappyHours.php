<?php

namespace App\Filament\Resources\HappyHourResource\Pages;

use App\Filament\Resources\HappyHourResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHappyHours extends ListRecords
{
    protected static string $resource = HappyHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
