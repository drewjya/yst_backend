<?php
namespace App\Filament\Resources;

use App\Filament\Resources\TherapistResource\Pages;
use App\Filament\Resources\TherapistResource\RelationManagers\AttendancesRelationManager;
use App\Filament\Resources\TherapistResource\RelationManagers\TherapistServicesRelationManager;
use App\Models\Therapist;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TherapistResource extends Resource
{
    protected static ?string $model = Therapist::class;

    protected static ?string $navigationGroup = 'Configuration';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->disabledOn('edit'),
                TextInput::make('no')->type('number')->disabledOn('edit'),
                Select::make('gender')
                    ->options([
                        'male'   => 'Laki-Laki',
                        'female' => 'Perempuan',
                    ])->native(false)->disabledOn('edit'),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm(
                        [
                            TextInput::make('name'),
                            TextInput::make('description'),
                        ]
                    )->disabledOn('edit'),

                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()

                    ->createOptionForm(
                        [
                            TextInput::make('name'),
                            TextInput::make('description'),
                        ]
                    )
                    ->label('Tags'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('name'),
                TextColumn::make('no'),
                TextColumn::make('branch.name'),

                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn(string $state) => [
                        'male'   => 'Laki-Laki',
                        'female' => 'Perempuan',
                    ][$state] ?? '-'),

                TextColumn::make('todayAttendance.check_in')
                    ->label('Check In')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-'),

                TextColumn::make('todayAttendance.check_out')
                    ->label('Check Out')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-'),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tags')
                    ->label('Filter by Tag')
                    ->relationship('tags', 'name')
                    ->multiple(),
                Tables\Filters\Filter::make('havenotcheckedin')
                    ->query(fn($query) => $query->whereDoesntHave('attendances', function ($q) {
                        $q->whereDate('date', now()->toDateString());
                    }))
                    ->label("Belum Check-In")

                ,

            ])
            ->actions([

                Tables\Actions\Action::make('check_in')
                    ->label('Check In')
                    ->color('success')
                    ->action(function (Therapist $record) {
                        $today            = now()->toDateString();
                        $alreadyCheckedIn = $record->attendances()
                            ->whereDate('date', $today)
                            ->exists();

                        if (! $alreadyCheckedIn) {
                            $record->attendances()->create([
                                'date'     => $today,
                                'check_in' => now(),
                            ]);
                        }
                    })
                    ->visible(fn(Therapist $record) => ! $record->attendances()
                            ->whereDate('date', now()->toDateString())
                            ->exists()),
                Tables\Actions\Action::make('check_out')
                    ->label('Check Out')
                    ->color('success')
                    ->action(function (Therapist $record) {
                        $today      = now()->toDateString();
                        $attendance = $record->attendances()
                            ->whereDate('date', $today)->first();

                        if ($attendance && ! $attendance->check_out) {
                            $attendance->update(['check_out' => now()]);
                        }

                    })
                    ->visible(function ($record) {
                        $attendance = $record->attendances()
                            ->whereDate('date', now()->toDateString()
                            )->first();

                        if ($attendance && ! $attendance->check_out) {
                            return true;
                        }

                        return false;
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AttendancesRelationManager::class,
            TherapistServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTherapists::route('/'),
            'create' => Pages\CreateTherapist::route('/create'),
            'edit'   => Pages\EditTherapist::route('/{record}/edit'),
        ];
    }
}
