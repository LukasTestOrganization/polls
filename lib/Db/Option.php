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

/**
 * @method int getId()
 * @method void setId(integer $value)
 * @method int getPollId()
 * @method void setPollId(integer $value)
 * @method string getPollOptionText()
 * @method void setPollOptionText(string $value)
 * @method int getTimestamp()
 * @method void setTimestamp(integer $value)
 * @method int getOrder()
 * @method void setOrder(integer $value)
 * @method int getConfirmed()
 * @method void setConfirmed(integer $value)
 * @method int getDuration()
 * @method void setDuration(integer $value)
 */
class Option extends Entity implements JsonSerializable {

	/** @var int $pollId */
	protected $pollId;

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


	public function jsonSerialize() {
		if (intval($this->timestamp) > 0) {
			$timestamp = $this->timestamp;
		} elseif (strtotime($this->pollOptionText)) {
			$timestamp = strtotime($this->pollOptionText);
		} else {
			$timestamp = 0;
		}

		return [
			'id' => intval($this->id),
			'pollId' => intval($this->pollId),
			'pollOptionText' => htmlspecialchars_decode($this->pollOptionText),
			'timestamp' => intval($timestamp),
			'order' => intval($timestamp ? $timestamp : $this->order),
			'confirmed' => intval($this->confirmed),
			'duration' => intval($this->duration),
			'rank' => $this->rank,
			'no' => $this->no,
			'yes' => $this->yes,
			'maybe' => $this->maybe,
			'realNo' => $this->realNo,
			'votes' => $this->votes,
			'isBookedUp' => $this->isBookedUp,
		];
	}
}
