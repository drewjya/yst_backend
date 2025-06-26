<?php
namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers\HappyHoursRelationManager;
use App\Filament\Resources\BranchResource\Widgets\StatsOverviewWidget;
use App\Models\Branch;
use App\Models\Service;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $model           = Branch::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('address'),
                TextInput::make('phone_number')->label('Phone Number'),
                TextInput::make('open_hour')->label('Jam Buka')->type('time')->required(),
                TextInput::make('close_hour')->label('Jam Tutup')->type('time')->required(),

                TableRepeater::make('branch_service')
                    ->label('Branch Services')
                    ->headers([
                        Header::make('Service'),
                        Header::make('Price'),
                    ])
                    ->columnSpanFull()
                    ->relationship('branchServices')
                    ->schema([
                        Select::make('service_id')
                            ->label('Service')
                            ->options(function (callable $get, callable $set, $state) {
                                return Service::query()->whereNotIn(
                                    'id',
                                    collect($get('../../branch_service'))->pluck('service_id')->diff([$state])
                                )->pluck('name', 'id');

                            })
                            ->required()
                            ->reactive()
                            ->preload()

                            ->columnSpan([
                                'md' => 5,
                            ])
                            ->searchable(),

                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->required()
                        ,
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('address'),
                TextColumn::make('phone_number'),
                TextColumn::make('open_hour'),
                TextColumn::make('close_hour'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
            HappyHoursRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit'   => Pages\EditBranch::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }
        $user = \Filament\Facades\Filament::auth()->user();
        return $user?->hasRole('Super Admin') ?? false;
    }
}
