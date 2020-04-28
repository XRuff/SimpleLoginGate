<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Identity;

use Nette\Database\IRow;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

/**
 * Github Identity
 */
class Github extends BaseIdentity
{
	public const
		TABLE_NAME = 'st_users',
		COLUMN_ID = 'id',
		COLUMN_USERNAME = 'login',
		COLUMN_EMAIL = 'email',
		COLUMN_NAME = 'name',
		COLUMN_LASTNAME = 'lastname',
		COLUMN_LOCALE = 'locale',
		COLUMN_IMAGE = 'image',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role',
		COLUMN_DATE = 'date_added';

	public const
		COLUMN_GITHUB_UID = 'github_uid',
		COLUMN_GITHUB_TOKEN = 'token',
		COLUMN_GITHUB_USER_ID = 'users_id',
		COLUMN_GITHUB_DATE_UPDATED = 'date_updated';

	/** @var string $table */
	public $table = 'users_github';

	/**
	 * @param int $fbId Github UID
	 * @return IRow
	 */
	public function findById($fbId): ?IRow
	{
		return $this->getTable()->where(self::COLUMN_GITHUB_UID, $fbId)->fetch();
	}

	/**
	 * @param int $userId User ID
	 * @return IRow
	 */
	public function getUser($userId): ?IRow
	{
		return $this->getTable()->where(self::COLUMN_GITHUB_USER_ID, $userId)->fetch();
	}

	/**
	 * Delete user's connection with network profile
	 *
	 * @param int $userId User ID
	 * @return int Number of affected rows
	 */
	public function remove($userId): int
	{
		return $this->getTable()->where(self::COLUMN_GITHUB_USER_ID, $userId)->delete();
	}

	/**
	 * @param string $userId User ID
	 * @param string $uid Github UID
	 * @param string $login
	 * @return IRow|int
	 */
	public function addIdentity(string $userId, string $uid, ?string $login = null)
	{
		$newFbUser = [
			self::COLUMN_GITHUB_USER_ID => $userId,
			self::COLUMN_GITHUB_UID => $uid,
			self::COLUMN_USERNAME => $login,
			self::COLUMN_DATE => new DateTime(),
		];

		return $this->getTable()->insert($newFbUser);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function prepareIdentity(\StdClass $fbIdentity, ?string $email = null): array
	{
		if (Strings::contains($fbIdentity->name, ' ')) {
			list($firstName, $lastName) = explode(' ', $fbIdentity->name);
		} else {
			$firstName = $fbIdentity->name;
			$lastName = '';
		}

		$newUserData = [
			self::COLUMN_ROLE => 'user',
			self::COLUMN_NAME => $firstName,
			self::COLUMN_LASTNAME => $lastName,
			self::COLUMN_DATE => new DateTime(),
		];

		if ($email) {
			$newUserData[self::COLUMN_USERNAME] = $email;
			$newUserData[self::COLUMN_EMAIL] = $email;
		}

		if (isset($fbIdentity->locale)) {
			$newUserData[self::COLUMN_LOCALE] = $fbIdentity->locale;
		}

		if (isset($fbIdentity->avatar_url)) {
			$newUserData[self::COLUMN_IMAGE] = $fbIdentity->avatar_url;
		}

		return $newUserData;
	}

	public function updateAccessToken(string $ibId, string $token): int
	{
		return $this->getTable()
			->where(self::COLUMN_GITHUB_UID, $ibId)
			->update([
				self::COLUMN_GITHUB_TOKEN => $token,
				self::COLUMN_GITHUB_DATE_UPDATED => new DateTime(),
			]);
	}

}
