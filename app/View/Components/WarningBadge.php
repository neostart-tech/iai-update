<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WarningBadge extends Component
{
	public function __construct(public string $message, public string $class = 'warning')
	{
	}

	public function render(): View
	{
		return view('components.warning-badge');
	}
}
