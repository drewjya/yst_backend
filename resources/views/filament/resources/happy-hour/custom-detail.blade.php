<x-filament::page>
    <x-filament::section label="Happy Hour Details">
        <div class="grid grid-cols-2 gap-4">
            <div><strong>Branch:</strong> {{ $record->branch->name }}</div>
            <div><strong>Days:</strong> {{ implode(', ', $record->days ?? []) }}</div>
            <div><strong>Start Time:</strong> {{ $record->start_time }}</div>
            <div><strong>End Time:</strong> {{ $record->end_time }}</div>
        </div>
    </x-filament::section>

    <x-filament::section label="Service Promos">
        <livewire:happy-hour-service-form :happyHourId="$record->id" />
    </x-filament::section>
</x-filament::page>
