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

class RecoveryPasswordFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var IUserManager */
	private $userManager;

	/** @var ITokenManager */
	private $tokenManager;

	/** @var Control $parent */
	private $parent;

	/** @var Email */
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
		$form = $this->factory->create();
		$this->parent = $parent;
		$form->setTranslator($this->translator);

		$form->addPassword('password', 'Password')
				->setRequired('Enter the new password.')
				->addRule(Form::MIN_LENGTH, 'Password must be at least% d characters.', $this->config->passwordLength);

		$form->addPassword('password2', 'common.sign.password2')
				->setRequired('common.sign.password2required')
				->addRule(Form::EQUAL, 'common.sign.passwordequal', $form['password']);

		$form->addSubmit('send', 'common.sign.recoverpassword.changepassword');
		$form->addHidden('token');

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
	}

	public function formSucceeded(Form $form, RecoveryPasswordValues $values): void
	{
		$tokenDb = $this->tokenManager->getToken($values->token, 'password');
		$token = $tokenDb->fetch();

		if (isset($token->token)) {
			// pripravim data z formulare pro ulozeni do db
			$values->id = $token->user_id;
			unset($values->password2);
			unset($values->token);
			// najdu uzivatele podle tokenu
			$user = $this->userManager->findBy(['id' => $token->user_id])->fetch();
			// oznacim token jako pouzity
			$this->tokenManager->useToken($token, $user);

			if ($result = $this->userManager->changePassword($token->user_id, $values)) {
				try {
					// poslu mail o uspesne zmene hesla
					$this->sendChangePasswordSuccessEmail($user);
					$this->parent->flashMessage('Your password has been successfully changed.', 'success');
				} catch (\Exception $e) {
					Debugger::log($e, 'simplelogin');
				} catch (\Error $e) {
					Debugger::log($e, 'simplelogin');
				}
				$this->parent->redirect(':Homepage:default');
			} else {
				$this->parent->flashMessage('There was an error when changing your password.', 'error');
			}
		}
	}

	public function sendChangePasswordSuccessEmail(IRow $user): void
	{
		$messageHi = $this->translator->translate('Hi');
		$message = $this->translator->translate('your password has been successfully changed.');

		$this->email->send(
			$this->config->nofityEmail,
			$user->login,
			$this->translator->translate('The password has been changed'),
			$messageHi . ' ' . $user->name . ",\n\n" . $message
		);
	}

}
