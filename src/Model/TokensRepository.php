<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Model;

use Nette\Database\Context;
use Nette\Database\IRow;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;
use Ramsey\Uuid\Uuid;
use XRuff\App\Model\BaseDbModel;
use XRuff\SimpleLoginGate\Configuration;

class TokensRepository extends BaseDbModel implements ITokenManager
{
	/** @var string */
	public const STATUS_PENDING = 'pending';

	/** @var string */
	public const STATUS_USED = 'used';
	public const COLUMN_USERID = 'user_id';
	public const COLUMN_TOKEN = 'token';
	public const COLUMN_TYPE = 'type';
	public const COLUMN_STATUS = 'status';
	public const COLUMN_DATE = 'date_added';
	public const COLUMN_DATE_USED = 'date_used';

	public function __construct(Context $db, Configuration $config)
	{
		parent::__construct($db, $config->tables['tokens']);
	}

	public function addToken(int $userId, ?string $type = 'activation'): string
	{
		$uuid4 = Uuid::uuid4();
		$token = $uuid4->toString();

		$this->getTable()->insert(
			[
				self::COLUMN_USERID => $userId,
				self::COLUMN_TOKEN => $token,
				self::COLUMN_TYPE => $type,
				self::COLUMN_STATUS => self::STATUS_PENDING,
				self::COLUMN_DATE => new DateTime(),
				self::COLUMN_DATE_USED => null,
			]
		);

		return $token;
	}

	public function getToken(string $code, ?string $type = 'activation'): Selection
	{
		return $this->getTable()->where(self::COLUMN_TOKEN, $code)->where(self::COLUMN_TYPE, $type);
	}

	public function useToken(IRow $token, ?IRow $userSelection): int
	{
		// if ($userSelection) {
		// 	$userSelection->update(['active' => 1]);
		// }

		return $this->getTable()->where('id', $token->id)->update(
			[
				self::COLUMN_STATUS => self::STATUS_USED,
				self::COLUMN_DATE_USED => new DateTime(),
			]
		);
	}
}
