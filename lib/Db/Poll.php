<?php
/**
 * @copyright Copyright (c) 2017 Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 *
 * @author Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 * @author Kai Schröer <git@schroeer.co>
 * @author René Gieling <github@dartcafe.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Polls\Db;

use JsonSerializable;

use OCP\IUser;
use OCP\AppFramework\Db\Entity;
use OCA\Polls\Model\User;

/**
 * @method string getType()
 * @method void setType(string $value)
 * @method string getTitle()
 * @method void setTitle(string $value)
 * @method string getDescription()
 * @method void setDescription(string $value)
 * @method string getOwner()
 * @method void setOwner(string $value)
 * @method int getCreated()
 * @method void setCreated(integer $value)
 * @method int getExpire()
 * @method void setExpire(integer $value)
 * @method int getDeleted()
 * @method void setDeleted(integer $value)
 * @method string getAccess()
 * @method void setAccess(string $value)
 * @method int getAnonymous()
 * @method void setAnonymous(integer $value)
 * @method int getallowComment()
 * @method void setallowComment(integer $value)
 * @method int getAllowMaybe()
 * @method void setAllowMaybe(integer $value)
 * @method string getAllowProposals()
 * @method void setAllowProposals(string $value)
 * @method int getProposalsExpire()
 * @method void setProposalsExpire(integer $value)
 * @method int getVoteLimit()
 * @method void setVoteLimit(integer $value)
 * @method int getOptionLimit()
 * @method void setOptionLimit(integer $value)
 * @method string getShowResults()
 * @method void setShowResults(string $value)
 * @method int getAdminAccess()
 * @method void setAdminAccess(integer $value)
 * @method int getImportant()
 * @method void setImportant(integer $value)
 * @method int getHideBookedUp()
 * @method void setHideBookedUp(integer $value)
 * @method int getUseNo()
 * @method void setUseNo(integer $value)
 */
class Poll extends Entity implements JsonSerializable {
	public const TYPE_DATE = 'datePoll';
	public const TYPE_TEXT = 'textPoll';
	public const ACCESS_HIDDEN = 'hidden';
	public const ACCESS_PUBLIC = 'public';
	public const SHOW_RESULTS_ALWAYS = 'always';
	public const SHOW_RESULTS_CLOSED = 'closed';
	public const SHOW_RESULTS_NEVER = 'never';
	public const PROPOSAL_DISALLOW = 'disallow';
	public const PROPOSAL_ALLOW = 'allow';
	public const PROPOSAL_REVIEW = 'review';

	/** @var string $type */
	protected $type;

	/** @var string $title */
	protected $title;

	/** @var string $description */
	protected $description;

	/** @var string $owner */
	protected $owner;

	/** @var int $created */
	protected $created;

	/** @var int $expire */
	protected $expire;

	/** @var int $deleted */
	protected $deleted;

	/** @var string $access */
	protected $access;

	/** @var int $anonymous */
	protected $anonymous;


	/** @var int $allowMaybe */
	protected $allowMaybe;

	/** @var string $allowProposals */
	protected $allowProposals;

	/** @var string $proposalsExpire */
	protected $proposalsExpire;

	/** @var int $voteLimit*/
	protected $voteLimit;

	/** @var int $optionLimit*/
	protected $optionLimit;

	/** @var string $showResults */
	protected $showResults;

	/** @var int $adminAccess*/
	protected $adminAccess;

	/** @var int $important*/
	protected $important;

	/** @var int $allowComment*/
	protected $allowComment;

	/** @var int $hideBookedUp*/
	protected $hideBookedUp;

	/** @var int $useNo*/
	protected $useNo;


	public function __construct() {
		$this->addType('created', 'int');
		$this->addType('expire', 'int');
		$this->addType('deleted', 'int');
		$this->addType('anonymous', 'int');
		$this->addType('allowComment', 'int');
		$this->addType('allowMaybe', 'int');
		$this->addType('proposalsExpire', 'int');
		$this->addType('voteLimit', 'int');
		$this->addType('optionLimit', 'int');
		$this->addType('adminAccess', 'int');
		$this->addType('important', 'int');
		$this->addType('hideBookedUp', 'int');
		$this->addType('useNo', 'int');
	}

	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'type' => $this->getType(),
			'title' => $this->getTitle(),
			'description' => $this->getDescription(),
			'descriptionSafe' => $this->getDescriptionSafe(),
			'owner' => $this->getOwner(),
			'created' => $this->getCreated(),
			'expire' => $this->getExpire(),
			'deleted' => $this->getDeleted(),
			'access' => $this->getAccess(),
			'anonymous' => $this->getAnonymous(),
			'allowComment' => $this->getAllowComment(),
			'allowMaybe' => $this->getAllowMaybe(),
			'allowProposals' => $this->getAllowProposals(),
			'proposalsExpire' => $this->getProposalsExpire(),
			'voteLimit' => $this->getVoteLimit(),
			'optionLimit' => $this->getOptionLimit(),
			'showResults' => $this->getShowResults() === 'expired' ? Poll::SHOW_RESULTS_CLOSED : $this->getShowResults(),
			'adminAccess' => $this->getAdminAccess(),
			'ownerDisplayName' => $this->getDisplayName(),
			'important' => $this->getImportant(),
			'hideBookedUp' => $this->getHideBookedUp(),
			'useNo' => $this->getUseNo(),
		];
	}

	/**
	 * @return static
	 */
	public function deserializeArray(array $array): self {
		$this->setTitle($array['title'] ?? $this->getTitle());
		$this->setDescription($array['description'] ?? $this->getDescription());
		$this->setAccess($array['access'] ?? $this->getAccess());
		$this->setExpire($array['expire'] ?? $this->getExpire());
		$this->setAnonymous($array['anonymous'] ?? $this->getAnonymous());
		$this->setallowComment($array['allowComment'] ?? $this->getallowComment());
		$this->setAllowMaybe($array['allowMaybe'] ?? $this->getAllowMaybe());
		$this->setAllowProposals($array['allowProposals'] ?? $this->getAllowProposals());
		$this->setProposalsExpire($array['proposalsExpire'] ?? $this->getProposalsExpire());
		$this->setVoteLimit($array['voteLimit'] ?? $this->getVoteLimit());
		$this->setOptionLimit($array['optionLimit'] ?? $this->getOptionLimit());
		$this->setShowResults($array['showResults'] ?? $this->getShowResults());
		$this->setDeleted($array['deleted'] ?? $this->getDeleted());
		$this->setAdminAccess($array['adminAccess'] ?? $this->getAdminAccess());
		$this->setImportant($array['important'] ?? $this->getImportant());
		$this->setHideBookedUp($array['hideBookedUp'] ?? $this->getHideBookedUp());
		$this->setUseNo($array['useNo'] ?? $this->getUseNo());
		return $this;
	}

	public function getExpired(): bool {
		return (
			   $this->getExpire() > 0
			&& $this->getExpire() < time()
		);
	}

	public function getProposalsExpired(): bool {
		return (
			   $this->getProposalsExpire() > 0
			&& $this->getProposalsExpire() < time()
		);
	}

	public function getDescriptionSafe(): string {
		return htmlspecialchars($this->description);
	}

	public function getDisplayName(): string {
		return \OC::$server->getUserManager()->get($this->owner) instanceof IUser
			? \OC::$server->getUserManager()->get($this->owner)->getDisplayName()
			: $this->owner;
	}

	public function getOwnerUserObject(): User {
		return new User($this->owner);
	}
}
