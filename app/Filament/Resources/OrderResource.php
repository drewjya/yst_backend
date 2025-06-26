<?php
namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\BranchService;
use App\Models\HappyHour;
use App\Models\HappyHourService;
use App\Models\Order;
use App\Models\Service;
use App\Models\Therapist;
use Carbon\Carbon;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                TextInput::make('guest_phone_number'),

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

                ViewField::make('order_status_display')
                    ->label('Order Status')
                    ->view('filament.forms.components.order-status-badge')
                    ->viewData([])
                    ->visible(fn($get) => in_array($get('order_status'), ['Complete', 'Cancelled'])),

                // Placeholder::make('order_status_display')
                //     ->label('Order Status')
                //     ->content(fn( $get) => new HtmlString("<span class='inline-flex items-center rounded-full px-3 py-0.5 text-sm font-medium " .
                //         match ($get('order_status')) {
                //             'Pending'              => 'bg-yellow-100 text-yellow-800',
                //             'Confirmed'            => 'bg-blue-100 text-blue-800',
                //             'Reschedule'           => 'bg-purple-100 text-purple-800',
                //             'Ongoing'              => 'bg-indigo-100 text-indigo-800',
                //             'Complete'             => 'bg-green-100 text-green-800',
                //             'Cancelled'            => 'bg-red-100 text-red-800',
                //             default                => 'bg-gray-100 text-gray-800',
                //         } .
                //         "'>{$get('order_status')}</span>"))
                //     ->visible(fn( $get) => in_array($get('order_status'), ['Complete', 'Cancelled']))
                //     ->disabled()
                //     ->dehydrated(false)
                //     ->visibleOn('edit')
                //     ->reactive()
                //     ->extraAttributes(['class' => 'pt-2'])
                //     ->columnSpanFull(),

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
                // ->disabledOn('edit')
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
                    ->visible(fn(callable $get) => $get('therapist_gender') && $get('branch_id')),
                // Select::make('services_data')
                //     ->label('Services')
                //     ->multiple()
                //     ->options(function (callable $get) {
                //         $branchId    = $get('branch_id');
                //         $therapistId = $get('therapist_id');

                //         if (! $branchId || ! $therapistId) {
                //             return [];
                //         }

                //         // Get tags for the therapist
                //         $tagIds = Therapist::find($therapistId)?->tags()->pluck('tags.id');

                //         // Filter services by branch and matching tags
                //         return Service::whereHas('branches', fn($q) => $q->where('branches.id', $branchId))
                //             ->whereIn('tag_id', $tagIds)
                //             ->pluck('name', 'id');
                //     })
                //     ->reactive()

                //     ->visible(fn(callable $get) => $get('therapist_id'))
                //     ->native(false),

                Placeholder::make('price_preview')
                    ->label('Price Preview')
                    ->content(function (callable $get) {
                        $serviceIds = $get('services_data');
                        $branchId   = $get('branch_id');

                        if (! $branchId || empty($serviceIds)) {
                            return 'No services selected.';
                        }

                        $now       = Carbon::now();
                        $today     = strtolower($now->format('l')); // e.g., "monday"
                        $timeNow   = $now->format('H:i:s');
                        $total     = 0;
                        $happyHour = HappyHour::where('branch_id', $branchId)
                            ->whereJsonContains('days', $today)
                            ->where('start_time', '<=', $timeNow)
                            ->where('end_time', '>=', $timeNow)
                            ->first();

                        $html = '<ul>';
                        foreach ($serviceIds as $serviceId) {
                            $branchService = BranchService::where('branch_id', $branchId)
                                ->where('service_id', $serviceId)
                                ->first();

                            if (! $branchService) {
                                continue;
                            }

                            $price = $branchService->price;

                            if ($happyHour) {
                                $happyHourService = HappyHourService::where('happy_hour_id', $happyHour->id)
                                    ->where('branch_service_id', $branchService->id)
                                    ->first();

                                if ($happyHourService) {
                                    $price = $happyHourService->promo_price;
                                }
                            }
                            $total       = $total + $price;
                            $serviceName = $branchService->service->name ?? 'Unknown Service';
                            $html .= "<li><strong>{$serviceName}</strong>: Rp " . number_format($price, 0, ',', '.') . "</li>";
                        }
                        $html .= "<li><strong>Total</strong> Rp " . number_format($total, 0, ',', '.') . "</li>";
                        $html .= '</ul>';

                        return new HtmlString($html);
                    })->reactive()->columnSpanFull(),
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
            //
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
