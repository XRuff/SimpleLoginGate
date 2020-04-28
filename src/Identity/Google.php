<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Identity;

use Nette\Database\IRow;
use Nette\Utils\DateTime;

/**
 * Google Identity
 */
class Google extends BaseIdentity
{
	public const
		TABLE_NAME = 'st_users',
		COLUMN_ID = 'id',
		COLUMN_USERNAME = 'login',
		COLUMN_EMAIL = 'email',
		COLUMN_NAME = 'name',
		COLUMN_LASTNAME = 'lastname',
		COLUMN_GENDER = 'gender',
		COLUMN_LOCALE = 'locale',
		COLUMN_IMAGE = 'image',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role',
		COLUMN_DATE = 'date_added';
	public const
		TABLE = 'users_google',
		COLUMN_GOOGLE_UID = 'google_uid',
		COLUMN_GOOGLE_TOKEN = 'token',
		COLUMN_GOOGLE_USER_ID = 'users_id',
		COLUMN_GOOGLE_DATE_UPDATED = 'date_updated';

	/** @var string $table */
	public $table = 'users_google';

	/**
	 * @param string $googleId Google UID
	 */
	public function findById(string $googleId): ?IRow
	{
		return $this->getTable()->where(self::COLUMN_GOOGLE_UID, $googleId)->fetch();
	}

	/**
	 * @param int $userId User ID
	 * @return IRow
	 */
	public function getUser(int $userId): ?IRow
	{
		return $this->getTable()->where(self::COLUMN_GOOGLE_USER_ID, $userId)->fetch();
	}

	/**
	 * Delete user's connection with network profile
	 *
	 * @param int $userId User ID
	 * @return int Number of affected rows
	 */
	public function remove(int $userId): int
	{
		return $this->getTable()->where(self::COLUMN_GOOGLE_USER_ID, $userId)->delete();
	}

	/**
	 * @param int $userId User ID
	 * @param int $googleId Google+ UID
	 * @param string $login
	 * @return IRow|int|bool
	 */
	public function addIdentity(int $userId, int $googleId, $login = null)
	{
		$newFbUser = [
			self::COLUMN_GOOGLE_USER_ID => $userId,
			self::COLUMN_GOOGLE_UID => $googleId,
			self::COLUMN_USERNAME => $login,
			self::COLUMN_DATE => new DateTime(),
		];

		return $this->getTable()->insert($newFbUser);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function prepareIdentity(\StdClass $googleIdentity, ?string $email = null, bool $resetRole = false): array
	{
		$name = explode(' ', $googleIdentity->name);

		$newUserData = [
			self::COLUMN_ROLE => 'user',
			self::COLUMN_NAME => $name[0],
			self::COLUMN_LASTNAME => $name[1],
			self::COLUMN_DATE => new DateTime(),
		];

		if ($resetRole) {
			unset($newUserData['role']);
		}

		if ($email) {
			$newUserData[self::COLUMN_USERNAME] = $email;
			$newUserData[self::COLUMN_EMAIL] = $email;
		}

		if ($googleIdentity->givenName) {
			$newUserData[self::COLUMN_NAME] = $googleIdentity->givenName;
		}

		if ($googleIdentity->familyName) {
			$newUserData[self::COLUMN_LASTNAME] = $googleIdentity->familyName;
		}

		if (isset($googleIdentity->gender)) {

			if ($googleIdentity->gender === 'male') {
				$googleIdentity->gender = 'm';
			}

			if ($googleIdentity->gender === 'female') {
				$googleIdentity->gender = 'f';
			}

			$newUserData[self::COLUMN_GENDER] = $googleIdentity->gender;
		}

		if (isset($googleIdentity->locale)) {
			$newUserData[self::COLUMN_LOCALE] = $googleIdentity->locale === 'cs' ? 'cs_CZ' : $googleIdentity->locale;
		}

		if (isset($googleIdentity->picture)) {
			$newUserData[self::COLUMN_IMAGE] = $googleIdentity->picture;
		}

		return $newUserData;
	}

	/**
	 * @param string $googleId
	 * @param array<string, string> $token
	 * */
	public function updateAccessToken(string $googleId, array $token): int
	{
		return $this->getTable()
			->where(self::COLUMN_GOOGLE_UID, $googleId)
			->update([
				self::COLUMN_GOOGLE_TOKEN => $token['access_token'],
				self::COLUMN_GOOGLE_DATE_UPDATED => new DateTime(),
			]);
	}
}
