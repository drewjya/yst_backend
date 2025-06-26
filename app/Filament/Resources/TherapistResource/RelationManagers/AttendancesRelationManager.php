<?php
namespace App\Filament\Resources\TherapistResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    // public function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\TextInput::make('Attendance Histories')
    //                 ->required()
    //                 ->maxLength(255),
    //         ]);
    // }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Attendance Histories')
            ->columns([
                TextColumn::make('date')->label('Tanggal')->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d M Y') : '-'),
                TextColumn::make('check_in')->label('Check In')->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-'),
                TextColumn::make('check_out')->label('Check Out')->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-'),
                
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
            ]);
    }
}
