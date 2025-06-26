<?php
namespace App\Filament\Resources\BranchResource\RelationManagers;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HappyHoursRelationManager extends RelationManager
{
    protected static string $relationship = 'happyHours';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                CheckboxList::make('days')
                    ->label('Active Days')
                    ->options([
                        'monday'    => 'Senin',
                        'tuesday'   => 'Selasa',
                        'wednesday' => 'Rabu',
                        'thursday'  => 'Kamis',
                        'friday'    => 'Jumat',
                        'saturday'  => 'Sabtu',
                        'sunday'    => 'Minggu',
                        'holiday'   => 'Libur Nasional',
                    ])
                    ->columns(4)
                    ->searchable()
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('start_time')->label('Start Time')->type('time')->required(),
                TextInput::make('end_time')->label('End Time')->type('time')->required(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No Happy Hour Set')

            ->columns([
                Tables\Columns\TextColumn::make('days')->formatStateUsing(function ($state) {
                    $labels = [
                        'monday'    => 'Senin',
                        'tuesday'   => 'Selasa',
                        'wednesday' => 'Rabu',
                        'thursday'  => 'Kamis',
                        'friday'    => 'Jumat',
                        'saturday'  => 'Sabtu',
                        'sunday'    => 'Minggu',
                        'holiday'   => 'Libur Nasional',
                    ];

                    $array = explode(", ", $state);

                    return implode(', ', array_map(fn($day) => $labels[$day] ?? ucfirst($day), $array));
                }),

                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn($livewire) => $livewire->ownerRecord->happyHours()->count() === 0),
            ])

            ->actions([
                Tables\Actions\Action::make('View Details')
                    ->url(fn($record) => route('filament.admin.resources.happy-hours.view', ['record' => $record]))
                    ->icon('heroicon-m-arrow-right'),

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
