<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Forms;

use Nette;
use Nette\Application\UI\Form;

class FormFactory
{
	use Nette\SmartObject;

	/**
	 * @return Form
	 */
	public function create()
	{
		return new Form;
	}
}
