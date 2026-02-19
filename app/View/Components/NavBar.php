<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class NavBar extends Component
{
    public ?string $profilePicture;
    public ?string $initials = null;

    /**
     * Create a new component instance.
     */
    public function __construct(?string $profilePicture)
    {
        $this->profilePicture = $profilePicture;
        if (Auth::user()) {

            $this->initials = collect(explode(' ', Auth::user()->name))->map(fn($word) => strtoupper($word[0]))->implode('');
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.nav-bar');
    }
}
