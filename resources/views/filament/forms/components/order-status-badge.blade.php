@php
    $status = $get('order_status') ?? '-';
    $color = match($status) {
        'Pending' => 'warning',
        'Confirmed' => 'info',
        'Reschedule' => 'purple',
        'Ongoing' => 'primary',
        'Complete' => 'success',
        'Cancelled' => 'danger',
        default => 'gray',
    };
@endphp

<x-filament::badge color="{{ $color }}">
    <span style="font-size: 1rem;">
        {{ $status }}
    </span>
</x-filament::badge>
