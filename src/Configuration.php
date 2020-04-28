<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate;

use Nette;

class Configuration
{
	use Nette\SmartObject;

	/** @var string $shortSession */
	public $shortSession;

	/** @var string $longSession */
	public $longSession;

	/** @var string $nofityEmail */
	public $nofityEmail;

	/** @var int $passwordLength */
	public $passwordLength;

	/** @var bool $loginAfterRegistration */
	public $loginAfterRegistration;

	/** @var bool $activeAfterRegistration */
	public $activeAfterRegistration;

	/** @var array<string> $tables */
	public $tables;

	/** @var array<string> $socialNetworks */
	public $socialNetworks;

	/**
	 * @param string $shortSession
	 * @param string $longSession
	 * @param string $nofityEmail
	 * @param int $passwordLength
	 * @param bool $loginAfterRegistration
	 * @param bool $activeAfterRegistration
	 * @param array<string> $tables
	 * @param array<string> $socialNetworks
	 */
	public function __construct(
		string $shortSession,
		string $longSession,
		string $nofityEmail,
		int $passwordLength,
		bool $loginAfterRegistration,
		bool $activeAfterRegistration,
		array $tables,
		array $socialNetworks
	)
	{
		$this->shortSession = $shortSession;
		$this->longSession = $longSession;
		$this->nofityEmail = $nofityEmail;
		$this->passwordLength = $passwordLength;
		$this->loginAfterRegistration = $loginAfterRegistration;
		$this->activeAfterRegistration = $activeAfterRegistration;
		$this->tables = $tables;
		$this->socialNetworks = $socialNetworks;
	}
}
