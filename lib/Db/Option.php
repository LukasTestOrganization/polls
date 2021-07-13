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

use OCP\AppFramework\Db\Entity;
use OCP\IUser;

/**
 * @method int getId()
 * @method void setId(integer $value)
 * @method int getConfirmed()
 * @method void setConfirmed(integer $value)
 * @method int getDuration()
 * @method void setDuration(integer $value)
 * @method int getOrder()
 * @method void setOrder(integer $value)
 * @method string getOwner()
 * @method void setOwner(string $value)
 * @method int getPollId()
 * @method void setPollId(integer $value)
 * @method string getPollOptionText()
 * @method void setPollOptionText(string $value)
 * @method int getReleased()
 * @method void setReleased(int $value)
 * @method int getTimestamp()
 * @method void setTimestamp(integer $value)
 */
class Option extends Entity implements JsonSerializable {

	/** @var int $pollId */
	protected $pollId;

	/** @var string $owner */
	protected $owner;

	/** @var int $released */
	protected $released;

	/** @var string $pollOptionText */
	protected $pollOptionText;

	/** @var int $timestamp */
	protected $timestamp;

	/** @var int $order */
	protected $order;

	/** @var int $confirmed */
	protected $confirmed;

	/** @var int $duration */
	protected $duration;

	// public variables, not in the db
	/** @var int $rank */
	public $rank = 0;

	/** @var int $yes */
	public $yes = 0;

	/** @var int $no */
	public $no = 0;

	/** @var int $maybe */
	public $maybe = 0;

	/** @var int $realNo */
	public $realNo = 0;

	/** @var int $votes */
	public $votes = 0;

	/** @var bool $isBookedUp */
	public $isBookedUp = false;

	public function __construct() {
		$this->addType('released', 'int');
		$this->addType('pollId', 'int');
		$this->addType('timestamp', 'int');
		$this->addType('order', 'int');
		$this->addType('confirmed', 'int');
		$this->addType('duration', 'int');
	}

	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'pollId' => $this->getPollId(),
			'owner' => $this->getOwner(),
			'ownerDisplayName' => $this->getDisplayName(),
			'ownerIsNoUser' => $this->getOwnerIsNoUser(),
			'released' => $this->getReleased(),
			'pollOptionText' => $this->getPollOptionText(),
			'timestamp' => $this->getTimestamp(),
			'order' => $this->getOrder(),
			'confirmed' => $this->getConfirmed(),
			'duration' => $this->getDuration(),
			'rank' => $this->rank,
			'no' => $this->no,
			'yes' => $this->yes,
			'maybe' => $this->maybe,
			'realNo' => $this->realNo,
			'votes' => $this->votes,
			'isBookedUp' => $this->isBookedUp,
		];
	}

	public function getPollOptionText(): string {
		if ($this->getTimestamp() && $this->getDuration()) {
			return date('c', $this->getTimestamp()) . ' - ' . date('c', $this->getTimestamp() + $this->getDuration());
		} elseif ($this->getTimestamp() && !$this->getDuration()) {
			return date('c', $this->getTimestamp());
		}
		return htmlspecialchars_decode($this->pollOptionText);
	}

	public function getPollOptionTextEnd(): string {
		if ($this->getTimestamp()) {
			return date('c', $this->getTimestamp() + $this->getDuration());
		}
		return htmlspecialchars_decode($this->pollOptionText);
	}

	public function getPollOptionTextStart(): string {
		if ($this->getTimestamp()) {
			return date('c', $this->getTimestamp());
		}
		return htmlspecialchars_decode($this->pollOptionText);
	}

	public function getOrder(): int {
		if ($this->timestamp) {
			return $this->getTimestamp();
		}
		return $this->order;
	}

	// used for 1.9.0-beta1 installtions
	public function getOwner() {
		if ($this->owner === 'disallow') {
			return '';
		}
		return $this->owner;
	}

	public function getDisplayName(): ?string {
		if (!strncmp($this->getOwner(), 'deleted_', 8)) {
			return 'Deleted User';
		}
		return $this->getOwnerIsNoUser()
			? $this->owner
			: \OC::$server->getUserManager()->get($this->getOwner())->getDisplayName();
	}

	private function getOwnerIsNoUser(): bool {
		return !\OC::$server->getUserManager()->get($this->getOwner()) instanceof IUser;
	}
}
