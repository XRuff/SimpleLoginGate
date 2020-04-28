<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate;

use Nette;
use Nette\Security\Passwords;
use XRuff\SimpleLoginGate\Identity\Facebook;
use XRuff\SimpleLoginGate\Identity\Github;
use XRuff\SimpleLoginGate\Identity\Google;
use XRuff\SimpleLoginGate\Model\IUserManager;

class SocialNetworkRegistrator
{
	use Nette\SmartObject;

	/** @var IUserManager $userManager */
	private $userManager;

	/** @var string $socialNetwork */
	private $socialNetwork;

	/** @var Passwords $passwords */
	private $passwords;

	/** @var Configuration $config */
	private $config;

	/** @var bool $isNew */
	private $isNew = false;

	/** @var Facebook $facebook */
	public $facebook;

	/** @var Google $google */
	public $google;

	/** @var Github $github */
	public $github;

	public function __construct(
		Configuration $config,
		IUserManager $userManager,
		Passwords $passwords,
		Facebook $facebook,
		Google $google,
		Github $github
	)
	{
		$this->config = $config;
		$this->userManager = $userManager;
		$this->facebook = $facebook;
		$this->google = $google;
		$this->github = $github;
		$this->passwords = $passwords;
	}

	public function fromSocialNetwork($id, $identity)
	{
		$socialNetworkService = $this->getSocialNetworkService();

		$email = $this->grabEmail($id, $identity);

		$isUserPresent = $this->userManager->byEmail($email);

		$newUserData = $socialNetworkService->prepareIdentity($identity, $email);

		$user = $this->addOrUpdateUser($isUserPresent, $newUserData);

		$socialNetworkService->addIdentity($user->id, $identity->id, $identity->email);

		return (object) [
			'identity' => $user,
			'isNew' => $this->isNew,
		];
	}

	public function updateUserIfNeeded($storedSocialProfile, $currentSocialProfile)
	{
		$socialNetworkService = $this->getSocialNetworkService();
		$existing = $this->userManager->findBy(['id' => $storedSocialProfile->users_id])->fetch();
		if ($this->config->socialNetworks->onLoginUpdate) {
			$newUserData = $socialNetworkService->prepareIdentity($currentSocialProfile, null, true);
			$this->userManager->update($existing->id, $newUserData);
			$existing = $this->userManager->findBy(['id' => $storedSocialProfile->users_id])->fetch();
		}

		return $existing;
	}

	public function setSocialNetwork($socialNetwork)
	{
		$this->socialNetwork = $socialNetwork;
		return $this;
	}

	public function getSocialNetwork()
	{
		return $this->socialNetwork;
	}

	public function getSocialNetworkService()
	{
		return $this->{$this->getSocialNetwork()};
	}

	private function grabEmail(int $id, $identity)
	{
		if (isset($identity->email)) {
			$email = $identity->email;
		} else {
			$email = $id;
		}

		return $email;
	}

	private function addOrUpdateUser($presentUser, $newUserData)
	{
		if ($presentUser) {
			if ($this->config->socialNetworks->onLoginUpdate) {
				unset($newUserData['role']);
				$this->userManager->update($presentUser->id, $newUserData);
			}
		} else {
			$presentUser = $this->userManager->register($newUserData);
			$this->userManager->changePassword($presentUser->id, ['password' => $this->passwords->hash(time())]);
			$this->isNew = true;
		}

		return $presentUser;
	}

}
