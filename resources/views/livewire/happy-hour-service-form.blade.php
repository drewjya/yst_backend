<form wire:submit.prevent="save" class="space-y-4">
    @foreach ($services as $index => $service)
        <div class="grid grid-cols-3 gap-4 items-center">
            <select wire:model="services.{{ $index }}.service_id" class="w-full rounded">
                <option value="">Select Service</option>
                @foreach ($allServices as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>

            <input wire:model="services.{{ $index }}.promo_price" type="number" class="w-full rounded" placeholder="Promo Price" />

            <button type="button" wire:click="removeRow({{ $index }})" class="text-red-500 hover:underline">
                Remove
            </button>
        </div>
    @endforeach

    <div>
        <button type="button" wire:click="addRow" class="text-sm text-blue-500 hover:underline">
            + Add Service
        </button>
    </div>

    <div>
        <x-filament::button type="submit">Save</x-filament::button>
        @if (session()->has('saved'))
            <span class="text-green-600 ml-2">{{ session('saved') }}</span>
        @endif
    </div>
</form>
