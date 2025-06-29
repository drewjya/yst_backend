<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    function getTitle(): string|HtmlString
    {
        $record = $this->record;

        // You can return a custom title with badge (rendered manually or via view)
        $badgeHtml = view('filament.forms.components.order-status-badge', [
            'get' => fn ($key) => $record->{$key},
        ])->render();

        // $html = "<div class=\"flex gap-2 items-center\"><spn>Edit Order</span></div>";

        return new HtmlString("
        <div class='flex items-center gap-2'>
            <span class='text-2xl font-bold'>Edit Order #{$record->id}</span>
            {$badgeHtml}
        </div>
");
    }

}
