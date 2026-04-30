<?php

namespace App\View\Components;

use App\Libraries\Fuel\BrandLogoLibrary;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BrandBadge extends Component
{
    public string $label;

    public ?string $logoUrl;

    public bool $hasLogo;

    public string $initials;

    public function __construct(
        public ?string $brand = null,
        public string $size = 'md',
    ) {
        $resolved = app(BrandLogoLibrary::class)->resolve($brand);

        $this->label = $resolved['brand'];
        $this->logoUrl = $resolved['url'];
        $this->hasLogo = $resolved['has_logo'];
        $this->initials = $resolved['initials'];
    }

    public function render(): View|Closure|string
    {
        return view('components.brand-badge');
    }
}
