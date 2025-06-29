<?php
namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderDetails';

    public function formatHourMinutes($state)
    {
        $totalMinutes = $state;
        $hours        = floor($totalMinutes / 60);
        $minutes      = $totalMinutes % 60;

        return $hours > 0
        ? "{$hours}1 jam {$minutes} menit"
        : "{$minutes} menit";

    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('name')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service_name')
            ->columns([
                TextColumn::make('service_name'),
                TextColumn::make('service_price')
                    ->label('Price')
                    ->money('IDR', true) // Optional: adds currency formatting
                    ->summarize(Sum::make()
                            ->money('IDR', true)
                            ->label('')
                            ->extraAttributes(['class' => 'font-bold !text-black'])),
                TextColumn::make('duration')->label('Duration (minute)')
                    ->summarize(Sum::make()
                            ->label('')
                            ->formatStateUsing(fn($state) =>$this->formatHourMinutes($state))
                            ->extraAttributes(['class' => 'font-bold text-red-800'])
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
        ;
    }
}
