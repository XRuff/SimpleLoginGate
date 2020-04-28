<?php

declare(strict_types = 1);

namespace XRuff\SimpleLoginGate\Identity;

use Nette\Database\Context;
use XRuff\App\Model\BaseDbModel;

/**
 * Base Identity
 */
class BaseIdentity extends BaseDbModel
{
	/** @var string $table */
	public $table;

	/**
	 * @param Context $database
	 */
	public function __construct(Context $database)
	{
		parent::__construct($database, $this->table);
	}
}
