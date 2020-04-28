<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Model;

use Nette\Database\IRow;
use Nette\Database\Table\Selection;

interface ITokenManager {
	public function addToken(int $userId, ?string $type): string;

	public function getToken(string $code, ?string $type): Selection;

	public function useToken(IRow $token, ?IRow $user): int;
}
