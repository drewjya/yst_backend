<?php

namespace App\Livewire;

use App\Models\HappyHour;
use App\Models\Service;
use Livewire\Component;

class HappyHourServiceForm extends Component
{
    public $happyHourId;
    public $services = [];

    public function mount()
    {
        $happyHour = HappyHour::with('services')->find($this->happyHourId);
        $this->services = $happyHour->services->map(function ($s) {
            return [
                'service_id' => $s->id,
                'promo_price' => $s->pivot->promo_price,
            ];
        })->toArray();
    }

    public function addRow()
    {
        $this->services[] = ['service_id' => null, 'promo_price' => null];
    }

    public function removeRow($index)
    {
        unset($this->services[$index]);
        $this->services = array_values($this->services);
    }

    public function save()
    {
        $data = collect($this->services)->filter(fn ($s) => $s['service_id'])->keyBy('service_id');

        HappyHour::find($this->happyHourId)->services()->sync($data->mapWithKeys(function ($item, $id) {
        return [$id => ['promo_price' => $item['promo_price']]];
        })->toArray());

        session()->flash('saved', 'Services updated!');
    }

    public function render()
    {
        return view('livewire.happy-hour-service-form', [
            'allServices' => Service::pluck('name', 'id'),
        ]);
    }

}
