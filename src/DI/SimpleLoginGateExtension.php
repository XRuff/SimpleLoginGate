<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\DI;

use Nette;
use Nette\DI\Statement;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;
use XRuff\SimpleLoginGate\Forms;
use XRuff\SimpleLoginGate\Identity;
use XRuff\SimpleLoginGate\SocialNetworkRegistrator;

class SimpleLoginGateExtension extends Nette\DI\CompilerExtension
{
	/**
	 * @var array<mixed> $defaults
	 */
	private $defaults = [
		'shortSession' => '60 minutes',
		'longSession' => '14 days',
		'passwordLength' => 8,
		'nofityEmail' => null,
		'userManager' => null,
		'tokenManager' => null,
		'loginAfterRegistration' => false,
		'activeAfterRegistration' => true,
		'socialNetworks' => [
			'onLoginUpdate' => true,
		],
		'tables' => ['tokens' => 'st_users_activation'],
		'registrationFields' => [
			1 => [
				'firstname',
				'username',
				'password',
			],
		],
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$this->validateConfig($this->defaults);

		$config = $this->config;

		Validators::assert($config['nofityEmail'], 'string', 'nofityEmail');
		Validators::assert($config['shortSession'], 'string', 'shortSession');
		Validators::assert($config['longSession'], 'string', 'longSession');
		Validators::assert($config['passwordLength'], 'number', 'passwordLength');
		Validators::assert($config['tables'], 'array|list', 'tables');
		Validators::assert($config['socialNetworks'], 'array|list', 'socialNetworks');
		Validators::assert($config['loginAfterRegistration'], 'boolean', 'loginAfterRegistration');
		Validators::assert($config['activeAfterRegistration'], 'boolean', 'activeAfterRegistration');

		$configuration = $builder->addDefinition($this->prefix('slgConfig'))
			->setClass('XRuff\SimpleLoginGate\Configuration')
			->setArguments([
				$config['shortSession'],
				$config['longSession'],
				$config['nofityEmail'],
				$config['passwordLength'],
				$config['loginAfterRegistration'],
				$config['activeAfterRegistration'],
				$config['tables'],
				$config['socialNetworks'],
			]);

		if ($config['userManager'] instanceof Statement) {
			$userManager = $config['userManager']->entity;
			$builder->addDefinition($this->prefix('userManager'))
				->setClass($userManager);
		} else {
			throw new AssertionException("Please fix your configuration, expression 'userManager' does not look like a valid model.");
		}

		if ($config['tokenManager'] && $config['tokenManager'] instanceof Statement) {
			$tokenManager = $config['tokenManager']->entity;
			$builder->addDefinition($this->prefix('tokenManager'))
				->setClass($tokenManager);
		} else {
			$builder->addDefinition($this->prefix('tokenManager'))
				->setClass('XRuff\SimpleLoginGate\Model\TokensRepository');
		}

		$builder->addDefinition($this->prefix('fbIdentity'))
			->setClass(Identity\Facebook::class);

		$builder->addDefinition($this->prefix('googleIdentity'))
			->setClass(Identity\Google::class);

		$builder->addDefinition($this->prefix('githubIdentity'))
			->setClass(Identity\Github::class);

		$builder->addDefinition($this->prefix('socialNetworkRegistrator'))
			->setClass(SocialNetworkRegistrator::class);

		$builder->addDefinition($this->prefix('slgFormFactory'))
			->setClass(Forms\FormFactory::class);

		$builder->addDefinition($this->prefix('slgSignControlFactory'))
			->setClass(Forms\SignFormFactory::class);

		$builder->addDefinition($this->prefix('slgRegistrationControlFactory'))
			->setClass(Forms\RegistrationFormFactory::class);

		$builder->addDefinition($this->prefix('slgForgottenPasswordControlFactory'))
			->setClass(Forms\ForgottenPasswordFactory::class);

		$builder->addDefinition($this->prefix('slgRecoveryPasswordControlFactory'))
			->setClass(Forms\RecoveryPasswordFactory::class);
	}
}
