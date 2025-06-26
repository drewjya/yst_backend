<?php
namespace App\Filament\Resources\HappyHourResource\Pages;

use App\Filament\Resources\HappyHourResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHappyHour extends EditRecord
{
    protected static string $resource = HappyHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected array $servicePromos = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->servicePromos = $data['service_promos'] ?? [];
        unset($data['service_promos']);
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->branchServicePromos()->sync([]);

        foreach ($this->servicePromos as $item) {
            $this->record->branchServicePromos()->attach($item['branch_service_id'], [
                'promo_price' => $item['promo_price'],
            ]);
        }
    }

}
