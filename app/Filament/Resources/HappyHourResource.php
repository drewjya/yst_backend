<?php
namespace App\Filament\Resources;

use App\Filament\Resources\HappyHourResource\Pages;
use App\Models\Branch;
use App\Models\BranchService;
use App\Models\HappyHour;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HappyHourResource extends Resource
{
    protected static ?string $model = HappyHour::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->options(function () {
                        return Branch::whereDoesntHave('happyHours')
                            ->pluck('name', 'id');
                    })

                    ->searchable()
                    ->preload()
                    ->required(),
                CheckboxList::make('days')
                    ->label('Active Days')
                    ->options([
                        'monday'    => 'Monday',
                        'tuesday'   => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday'  => 'Thursday',
                        'friday'    => 'Friday',
                        'saturday'  => 'Saturday',
                        'sunday'    => 'Sunday',
                        'holiday'   => 'Holiday',
                    ])
                    ->columns(4)
                    ->searchable()
                    ->columnSpanFull()
                    ->required(),

                TextInput::make('start_time')->type('time')->required(),
                TextInput::make('end_time')->type('time')->required(),
                TableRepeater::make('service_promos')
                    ->label('Promo Services')
                    ->headers(
                        [
                            Header::make('Service'),
                            Header::make('Promo Price'),
                        ]
                    )
                    ->columnSpanFull()
                    ->schema([
                        Select::make('branch_service_id')
                            ->label('Service')
                            ->options(function (callable $get, callable $set, $state) {
                                $branchId    = $get('../../branch_id');
                                $selectedIds = collect($get('../../service_promos'))
                                    ->pluck('branch_service_id')
                                    ->filter()
                                    ->all();
                                return BranchService::with('service')
                                    ->where('branch_id', $branchId)
                                    ->when(! empty($selectedIds), function ($query) use ($selectedIds) {
                                        $query->whereNotIn('id', $selectedIds);
                                    })
                                    ->get()
                                    ->pluck('service.name', 'id');

                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('promo_price')
                            ->numeric()
                            ->required(),
                    ])
                    ->dehydrated(false), // handle manually

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No Happy Hour Set')

            ->columns([
                TextColumn::make('branch.name'),
                TextColumn::make('days')->formatStateUsing(function ($state) {
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

                TextColumn::make('start_time'),
                TextColumn::make('end_time'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHappyHours::route('/'),
            'create' => Pages\CreateHappyHour::route('/create'),
            'view'   => Pages\ViewHappyHour::route('/{record}'),
            'edit'   => Pages\EditHappyHour::route('/{record}/edit'),
        ];
    }
}
