<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EmptyTable extends Component
{
	public function __construct(private readonly string $content = 'Aucune donnée à afficher dans ce tableau', private readonly string $class = 'warning')
	{
	}

	public function render(): View
	{
		return view('components.empty-table')->with([
			'content' => $this->content,
			'class' => $this->class,
		]);
	}
}
