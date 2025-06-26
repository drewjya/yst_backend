<?php
namespace App\Filament\Resources\HappyHourResource\RelationManagers;

use App\Models\Service;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('service_id')
                    ->label('Service')
                    ->options(Service::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('promo_price')
                    ->label('Promo Price')
                    ->numeric()
                    ->required(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service_id')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Service'),
                Tables\Columns\TextColumn::make('service_id'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
