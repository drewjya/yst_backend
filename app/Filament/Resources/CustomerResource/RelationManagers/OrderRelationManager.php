<?php
namespace App\Filament\Resources\CustomerResource\RelationManagers;

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
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OrderRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->required()
                    ->reactive()
                    ->native(false)
                    ->afterStateUpdated(fn(callable $set) => $set('therapist_id', null)),
                TextInput::make('guest_phone_number'),
                Select::make('guest_gender')
                    ->options([
                        'male'   => 'Laki-Laki',
                        'female' => 'Perempuan',
                    ])
                    ->native(false),

                Select::make('therapist_gender')
                    ->options([
                        'male'   => 'Laki-Laki',
                        'female' => 'Perempuan',
                    ])
                    ->reactive()
                    ->native(false)
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
                Select::make('services_data')
                    ->label('Services')
                    ->multiple()
                    ->options(function (callable $get) {
                        $branchId    = $get('branch_id');
                        $therapistId = $get('therapist_id');

                        if (! $branchId || ! $therapistId) {
                            return [];
                        }

                        // Get tags for the therapist
                        $tagIds = Therapist::find($therapistId)?->tags()->pluck('tags.id');

                        // Filter services by branch and matching tags
                        return Service::whereHas('branches', fn($q) => $q->where('branches.id', $branchId))
                            ->whereIn('tag_id', $tagIds)
                            ->pluck('name', 'id');
                    })
                    ->reactive()
                    ->required()
                    ->visible(fn(callable $get) => $get('therapist_id'))
                    ->native(false),
                
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Order')
            ->columns([
                TextColumn::make('customer.name')
                    ->description(fn($record) => "{$record->customer->email} | {$record->customer->phone_number}")
                    ->searchable(),
                // TextColumn::make('transaction_id')->searchable(),
                TextColumn::make('therapist.name'),
                TextColumn::make('branch.name'),
                TextColumn::make('order_date')->label('Date'),
                TextColumn::make('order_time')->label('Time'),
                TextColumn::make('order_status')->label('Status'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function (Order $record, array $data) {
                        $branchId    = $data['branch_id'];
                        $selectedIds = $data['services_data'] ?? [];

                        if (empty($selectedIds)) {
                            return;
                        }

                        $now     = \Carbon\Carbon::now();
                        $today   = strtolower($now->format('l'));
                        $timeNow = $now->format('H:i:s');

                        $happyHour = \App\Models\HappyHour::where('branch_id', $branchId)
                            ->whereJsonContains('days', $today)
                            ->where('start_time', '<=', $timeNow)
                            ->where('end_time', '>=', $timeNow)
                            ->first();
                        foreach ($selectedIds as $serviceId) {
                            $branchService = \App\Models\BranchService::where('branch_id', $branchId)
                                ->where('service_id', $serviceId)
                                ->first();

                            if (! $branchService) {
                                continue;
                            }

                            $price = $branchService->price;

                            if ($happyHour) {
                                $happyHourService = \App\Models\HappyHourService::where('happy_hour_id', $happyHour->id)
                                    ->where('branch_service_id', $branchService->id)
                                    ->first();

                                if ($happyHourService) {
                                    $price = $happyHourService->promo_price;
                                }
                            }

                            $record->orderDetails()->create([
                                'service_id'    => $serviceId,
                                'service_name'  => $branchService->service->name ?? 'Unknown Service',
                                'service_price' => (int) $price,
                                'duration'      => $branchService->service->duration ?? 30,
                            ]);
                        }

                    }),
            ])
            ->actions([
                // ActionsViewAction::make(),
                // EditAction::make(),
                // DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
