<?php
namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderDetailsRelationManager;
use App\Models\Order;
use App\Models\Therapist;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->required()
                    ->reactive()
                    ->native(false)
                    ->disabledOn('edit')
                    ->afterStateUpdated(fn(callable $set) => $set('therapist_id', null)),
                TextInput::make('guest_phone_number')
                    ->disabledOn('edit'),

                ToggleButtons::make('order_status')
                    ->label(fn($get) => match ($get('order_status')) {
                        'Cancelled'      => "Order Status ",
                        'Complete'       => 'Order Status ',
                        default          => 'Change Order Status From '
                    } . $get('order_status'))
                    ->options(function ($get) {
                        return match ($get('order_status')) {
                            'Pending'    =>
                            [
                                'Confirmed' => "Confirmed",
                                'Cancelled' => "Cancelled",
                            ],
                            'Confirmed'  =>
                            [
                                'Reschedule' => "Reschedule",
                                'Ongoing'    => 'Ongoing',
                            ],
                            'Reschedule' =>
                            [
                                'Confirmed' => "Confirmed",
                            ],
                            'Ongoing'    =>
                            [
                                'Complete' => "Complete",
                            ],
                            default      => [

                            ],
                        };
                    })->visibleOn('edit') // only visible on edit
                    ->disabledOn('create')
                    ->hidden(fn($get) => in_array($get('order_status'), ['Complete', 'Cancelled']))
                    ->grouped(),

                ToggleButtons::make('guest_gender')
                    ->options([
                        'male'   => 'Laki-Laki',
                        'female' => 'Perempuan',
                    ])
                    ->disabledOn('edit')
                    ->grouped(),

                ToggleButtons::make('therapist_gender')
                    ->options([
                        'male'   => 'Laki-Laki',
                        'female' => 'Perempuan',
                    ])
                    ->grouped()
                    ->reactive()
                    ->disabledOn('edit')
                    ->visible(fn(callable $get) => $get('branch_id'))
                    ->afterStateUpdated(fn(callable $set) => $set('therapist_id', null)),
                Select::make('therapist_id')
                    ->label('Therapist')
                    ->relationship('therapist', 'name')
                    ->searchable()
                    ->preload()

                    ->options(function (callable $get) {
                        $branchId = $get('branch_id');
                        $gender   = $get('therapist_gender');

                        // Get therapists in the selected branch and gender
                        $therapists = Therapist::where('branch_id', $branchId)
                            ->where('gender', $gender)
                            ->get();

                        // If no services are selected, return all valid therapists

                        // Filter therapists who can perform ALL selected services
                        return $therapists->pluck('name', 'id');
                    })->reactive()
                    ->required()
                    ->native(false)
                    ->visible(fn(callable $get) => $get('therapist_gender') && $get('branch_id'))
                    // ->disabledOn(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->description(fn($record) => "{$record->customer->email} | {$record->customer->phone_number}")
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('transaction_id')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('therapist.name')
                    ->toggleable(),
                TextColumn::make('branch.name')
                    ->toggleable(),
                TextColumn::make('order_date')->label('Date')
                    ->toggleable(),
                TextColumn::make('order_time')->label('Time')
                    ->toggleable(),
                TextColumn::make('order_status')->label('Status')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            OrderDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
