<?php
namespace App\Filament\Resources\BranchResource\Widgets;

use App\Models\Branch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $branches = Branch::all();
        return $branches->map(function ($branch) {
            $adminCount = $branch->admins()->role('Branch Admin')->count();
            return Stat::make("{$branch->name} Admins", $adminCount)
                ->color('info');
        })->toArray();
    }
}
