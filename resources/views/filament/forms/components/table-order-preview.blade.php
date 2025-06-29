<style>
    .order-preview-grid {
        display: grid;
        grid-template-columns: auto auto;
        row-gap: 8px;
        column-gap: 16px;
        max-width: 400px;
    }

    
</style>

<div class="order-preview-grid">

<div>Service</div>
<div>Harga</div>

    @foreach ($items as $item)
        <div>{{ is_array($item['name']) ? $item['name']['label'] ?? '—' : $item['name'] }}</div>
        <div>Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
    @endforeach

    <div class="order-preview-total">Total</div>
    <div class="order-preview-total">Rp {{ number_format($total, 0, ',', '.') }}</div>
</div>
