<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use XRuff\SimpleLoginGate\Configuration;

class SignFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;

	/** @var ITranslator $translator */
	public $translator;

	/** @var Configuration $config */
	public $config;

	public function __construct(
		Configuration $config,
		FormFactory $factory,
		User $user,
		ITranslator $translator
	)
	{
		$this->factory = $factory;
		$this->user = $user;
		$this->translator = $translator;
		$this->config = $config;
	}

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addText('username', 'Username')
			->setRequired('Please enter your login name.');

		$form->addPassword('password', 'Password')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Keep me in');

		$form->addSubmit('send', 'Log in');

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
	}

	public function formSucceeded(Form $form, SignFormFactoryValues $values): void
	{
		if ($values->remember) {
			$this->user->setExpiration($this->config->longSession);
		} else {
			$this->user->setExpiration($this->config->shortSession);
		}

		try {
			$this->user->login($values->username, $values->password);
		} catch (AuthenticationException $e) {
			$form->addError($this->translator->translate('The login or password you entered is incorrect'));
		}
	}
}
