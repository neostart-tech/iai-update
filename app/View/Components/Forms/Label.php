<?php

namespace App\View\Components\Forms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Label extends Component
{
	public function __construct(public readonly string $content, public readonly string $for, public readonly string $class="form-label", public bool $required = true)
	{
	}

	public function render(): View
	{
		return view('components.forms.label');
	}
}
