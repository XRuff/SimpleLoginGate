<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Model;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

interface IUserManager {
	public function byEmail(string $email): ?IRow;

	/** @param array<string, int|string> $by */
	public function findBy(array $by): Selection;

	/**
	 * @param int $userId
	 * @param mixed $values
	 */
	public function changePassword(int $userId, $values): int;

	public function isUsernameRegistered(string $value): bool;

	public function add(string $username, string $password, string $name): ActiveRow;
}
