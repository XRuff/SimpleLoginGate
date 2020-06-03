<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate;

use Nette;

class SocialNetworkResult
{
	use Nette\SmartObject;

	/** @var mixed $identity */
	public $identity;

	/** @var bool $isNew */
	public $isNew;

	/**
	 * @param mixed $identity
	 * @param bool $isNew
	 */
	public function __construct($identity, bool $isNew)
	{
		$this->identity = $identity;
		$this->isNew = $isNew;
	}
}
