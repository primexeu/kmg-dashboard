<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

// MODELS
use App\Models\User;
use App\Models\PythonApiComparison;

// RESOURCES (for URLs)
use App\Filament\Resources\UserResource;
use App\Filament\Resources\PythonApiComparisonResource;

class TopbarSearch extends Component
{
    public string $query = '';
    public array $results = [];
    public bool $open = false;
    protected int $limit = 5; // per resource

    public function updatedQuery(): void
    {
        $q = trim($this->query);
        $this->results = [];
        $this->open = false;

        if ($q === '' || Str::length($q) < 2) {
            return;
        }

        // Helper: uniform rows
        $map = function ($items, string $group, string $icon, callable $label, callable $sub, callable $url) {
            return $items->map(fn ($r) => [
                'label' => $label($r),
                'sub'   => $sub($r),
                'url'   => $url($r),
                'icon'  => $icon,
                'group' => $group,
            ])->toArray();
        };

        // --- Python API Comparisons (Küchenabgleich)
        $comparisons = PythonApiComparison::query()
            ->where(fn ($qq) => $qq
                ->where('order_number', 'like', "%{$q}%")
                ->orWhere('ab_number', 'like', "%{$q}%")
                ->orWhere('customer_name', 'like', "%{$q}%")
                ->orWhere('customer_number', 'like', "%{$q}%")
                ->orWhere('commission', 'like', "%{$q}%"))
            ->limit($this->limit)->get();

        $comparisons = $map(
            $comparisons, 'Küchenabgleich', 'heroicon-o-document-magnifying-glass',
            fn ($r) => $r->order_number . ' / ' . $r->ab_number,
            fn ($r) => "{$r->customer_name} • {$r->overall_status}",
            fn ($r) => PythonApiComparisonResource::getUrl('compare', ['record' => $r])
        );






        // --- Users
        $users = User::query()
            ->where(fn ($qq) => $qq
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%"))
            ->limit($this->limit)->get();

        $users = $map(
            $users, 'Users', 'heroicon-o-user',
            fn ($r) => $r->name,
            fn ($r) => $r->email,
            fn ($r) => UserResource::getUrl('view', ['record' => $r])
        );

        // Merge all
        $this->results = [
            ...$comparisons,
            ...$users,
        ];

        $this->open = !empty($this->results);
    }

    public function go(string $url)
    {
        return redirect()->to($url);
    }

    public function render()
    {
        return view('livewire.topbar-search');
    }
}
