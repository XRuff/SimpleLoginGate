<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Forms;

use Nette;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\IRow;
use Nette\Localization\ITranslator;
use Tracy\Debugger;
use XRuff\App\Model\Utils\Email;
use XRuff\SimpleLoginGate\Configuration;
use XRuff\SimpleLoginGate\Model\ITokenManager;
use XRuff\SimpleLoginGate\Model\IUserManager;

class ForgottenPasswordFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var IUserManager */
	public $userManager;

	/** @var ITokenManager */
	private $tokenManager;

	/** @var Control parent */
	private $parent;

	/** @var Email $email */
	private $email;

	/** @var ITranslator $translator */
	public $translator;

	/** @var Configuration $config */
	public $config;

	public function __construct(
		Configuration $config,
		FormFactory $factory,
		IUserManager $userManager,
		ITokenManager $tokenManager,
		Email $email,
		ITranslator $translator
	)
	{
		$this->config = $config;
		$this->factory = $factory;
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
		$form->addPassword('username', 'Username')
			->setType('email')
			->setRequired($this->translator->translate('common.sign.usernamerequiredsignup'))
			->addRule(Form::EMAIL, $this->translator->translate('common.sign.usernameformat'));

		$form->addSubmit('send', 'Send it to me');

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
	}

	public function formSucceeded(Form $form, ForgottenPasswordValues $values): void
	{
		$success = false;
		try {
			$user = $this->userManager->byEmail($values->username);
			if ($user) {
				$token = $this->generateNewToken($user->id);
				$this->sendRecoveryEmail($user, $token);
				$this->parent->flashMessage($this->translator->translate('We have sent instructions to the specified e-mail address.'), 'success');
				$success = true;
			} else {
				$form->addError($this->translator->translate('Please check the address you entered, as it does not appear to be in the system.'));
			}
		} catch (\Exception $e) {
			$form->addError('There was an error when changing your password.');
			Debugger::log($e, 'simplelogin');
		} catch (\Error $e) {
			$form->addError('There was an error when changing your password.');
			Debugger::log($e, 'simplelogin');
		}

		if ($success) {
			$this->parent->redirect('this');
		}
	}

	private function generateNewToken(int $userId): string
	{
		return $this->tokenManager->addToken($userId, 'password');
	}

	public function sendRecoveryEmail(IRow $user, string $token): void
	{
		$link = $this->parent->link('//Sign:recoverPassword', ['code' => $token]);

		$messageHi = $this->translator->translate('Hi');
		$message = $this->translator->translate('you requested link for reset access to your account.');
		$message2 = $this->translator->translate('Please, click on following link to set new password:');
		$message3 = $this->translator->translate('If you did not request this action, you can ignore this email.');
		$message4 = $this->translator->translate('Thanks');

		$this->email->send(
			$this->config->nofityEmail,
			$user->login,
			$this->translator->translate('common.sign.recoverpassword.subject'),
			$messageHi . ' ' . $user->name . ",\n\n" . $message . "\n" . $message2 . " \n\n" . $link . "\n\n" . $message3 . "\n\n" . $message4
		);
	}
}
