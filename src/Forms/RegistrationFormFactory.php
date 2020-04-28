<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Forms;

use Nette;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\TextInput;
use Nette\Localization\ITranslator;
use Nette\Mail\SendException;
use Nette\Security\IUserStorage;
use Nette\Security\User;
use Tracy\Debugger;
use XRuff\App\Model\Utils\Email;
use XRuff\SimpleLoginGate\Configuration;
use XRuff\SimpleLoginGate\Model\ITokenManager;
use XRuff\SimpleLoginGate\Model\IUserManager;

class RegistrationFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User $user */
	private $user;

	/** @var IUserManager $userManager */
	private $userManager;

	/** @var ITokenManager */
	private $tokenManager;

	/** @var Control $parent */
	private $parent;

	/** @var Email $email */
	private $email;

	/** @var ITranslator $translator */
	public $translator;

	/** @var Configuration $config */
	public $config;

	/** @var array<callable> $onSuccess */
	public $onSuccess = [];

	public function __construct(
		Configuration $config,
		FormFactory $factory,
		User $user,
		IUserManager $userManager,
		ITokenManager $tokenManager,
		Email $email,
		ITranslator $translator
	)
	{
		$this->config = $config;
		$this->factory = $factory;
		$this->user = $user;
		$this->userManager = $userManager;
		$this->tokenManager = $tokenManager;
		$this->email = $email;
		$this->translator = $translator;
	}

	/**
	 * @return Form
	 */
	public function create(Control $parent): Form
	{
		$this->parent = $parent;
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addText('username', 'common.sign.username')
			->setType('email')
			->setRequired('common.sign.usernamerequiredsignup')
				->addRule(Form::EMAIL, 'common.sign.usernameformat')
				->addRule([$this, 'isUsernameAvailable'], 'common.sign.usernametaken');

		$form->addText('name', 'Name');

		$form->addPassword('password', 'Password')
			->setRequired('Please enter your password.')
			->addRule(Form::MIN_LENGTH, 'Password must be at least% d characters.', $this->config->passwordLength);

		$form->addPassword('password2', 'common.sign.password2')
			->setRequired('common.sign.password2required')
			->addRule(Form::EQUAL, 'common.sign.passwordequal', $form['password']);

		$form->addSubmit('send', 'Sign up');

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
	}

	public function isUsernameAvailable(TextInput $item): bool
	{
		return !$this->userManager->isUsernameRegistered($item->value);
	}

	public function formSucceeded(Form $form, RegistrationFormValues $values): void
	{
		try {
			$newUser = $this->userManager->add($values->username, $values->password, $values->name);

			// log user in
			$this->user->setExpiration($this->config->shortSession, IUserStorage::CLEAR_IDENTITY);

			// if ($this->config->loginAfterRegistration) {
			// 	$this->user->login($values->username, $values->password);
			// }

		} catch (\Exception $e) {
			Debugger::log($e, 'login');
			$form->addError($this->translator->translate('common.sign.signupfailed'));
			return;
		}

		try {
			$this->onSuccess($values->password, $newUser, $this->config);
			$this->sendActivationEmail($newUser);
		} catch (SendException $e) {
			Debugger::log($e, 'login');
		}
	}

	public function sendActivationEmail(ActiveRow $user): void
	{
		$token = $this->tokenManager->addToken($user->id, 'activation');
		$link = $this->parent->link('//Sign:activation', ['code' => $token]);
		$this->email->send(
			$this->config->nofityEmail,
			$user->login,
			$this->translator->translate('common.sign.registrationemail.subject'),
			'Hi ' . $user->name . ",\n\nyour registration need just one more step.\nPlease, click on following link to confirm sign up process:\n\n" . $link . "\n\nIf you did not request this action, you can ignore this email.\n\nThanks.\n"
		);
	}
}
