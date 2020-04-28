<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Identity;

use Nette\Utils\DateTime;

/**
 * Facebook Identity
 */
class Facebook extends BaseIdentity
{
	public const
		COLUMN_ID = 'id',
		COLUMN_USERNAME = 'login',
		COLUMN_EMAIL = 'email',
		COLUMN_NAME = 'name',
		COLUMN_LASTNAME = 'lastname',
		COLUMN_GENDER = 'gender',
		COLUMN_LOCALE = 'locale',
		COLUMN_IMAGE = 'image',
		COLUMN_BIRTHDAY = 'birthday',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role',
		COLUMN_DATE = 'date_added';
	public const
		COLUMN_FB_UID = 'fb_uid',
		COLUMN_FB_TOKEN = 'token',
		COLUMN_FB_USER_ID = 'users_id',
		COLUMN_FB_DATE_UPDATED = 'date_updated';

	/** @var string $table */
	public $table = 'users_fb';

	/**
	 * @param int $fbId Facebook UID
	 * @return \Nette\Database\IRow|bool
	 */
	public function findById($fbId) {
		return $this->getTable()->where(self::COLUMN_FB_UID, $fbId)->fetch();
	}

	/**
	 * @param int $userId User ID
	 * @return \Nette\Database\IRow|bool
	 */
	public function getUser($userId) {
		return $this->getTable()->where(self::COLUMN_FB_USER_ID, $userId)->fetch();
	}

	/**
	 * Delete user's connection with network profile
	 *
	 * @param int $userId User ID
	 * @return int Number of affected rows
	 */
	public function remove($userId) {
		return $this->getTable()->where(self::COLUMN_FB_USER_ID, $userId)->delete();
	}

	/**
	 * @param int $userId User ID
	 * @param int $fbUID Facebook UID
	 * @param string $login
	 * @return \Nette\Database\IRow|int|bool
	 */
	public function addIdentity($userId, $fbUID, $login = null) {
		$newFbUser = [
			self::COLUMN_FB_USER_ID => $userId,
			self::COLUMN_FB_UID => $fbUID,
			self::COLUMN_USERNAME => $login,
			self::COLUMN_DATE => new DateTime(),
		];

		return $this->getTable()->insert($newFbUser);
	}

	/**
	 * @param \StdClass $fbIdentity Facebook identity from API
	 * @param string $email
	 * @return array<string, mixed>
	 */
	public function prepareIdentity(\StdClass $fbIdentity, ?string $email = null, ?bool $resetRole = false): array
	{
		list($firstName, $lastName) = explode(' ', $fbIdentity->name);

		$newUserData = [
			self::COLUMN_ROLE => 'user',
			self::COLUMN_NAME => $firstName,
			self::COLUMN_LASTNAME => $lastName,
			self::COLUMN_DATE => new DateTime(),
		];

		if ($resetRole) {
			unset($newUserData['role']);
		}

		if ($email) {
			$newUserData[self::COLUMN_USERNAME] = $email;
			$newUserData[self::COLUMN_EMAIL] = $email;
		}

		if ($fbIdentity->first_name) {
			$newUserData[self::COLUMN_NAME] = $fbIdentity->first_name;
		}

		if ($fbIdentity->last_name) {
			$newUserData[self::COLUMN_LASTNAME] = $fbIdentity->last_name;
		}

		if (isset($fbIdentity->birthday)) {
			$newUserData[self::COLUMN_BIRTHDAY] = $fbIdentity->birthday;
		}

		if (isset($fbIdentity->gender)) {

			if ($fbIdentity->gender === 'male') {
				$fbIdentity->gender = 'm';
			}

			if ($fbIdentity->gender === 'female') {
				$fbIdentity->gender = 'f';
			}

			$newUserData[self::COLUMN_GENDER] = $fbIdentity->gender;
		}

		if (isset($fbIdentity->locale)) {
			$newUserData[self::COLUMN_LOCALE] = $fbIdentity->locale;
		}

		if (isset($fbIdentity->picture) && $fbIdentity->picture->data && $fbIdentity->picture->data->url) {
			$newUserData[self::COLUMN_IMAGE] = $fbIdentity->picture->data->url;
		}

		return $newUserData;
	}

	/**
	 * @param int $ibId Facebook identity from API
	 * @param string $token Access Token
	 * @return int Number of affected rows
	 */
	public function updateAccessToken(int $ibId, string $token): int
	{
		return $this->getTable()
			->where(self::COLUMN_FB_UID, $ibId)
			->update([
				self::COLUMN_FB_TOKEN => $token,
				self::COLUMN_FB_DATE_UPDATED => new DateTime(),
			]);
	}

}
