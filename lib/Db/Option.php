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
			'order' => $this->orderCorrection(intval($this->timestamp), intval($this->order)),
			'confirmed' => intval($this->confirmed),
			'no' => 0,
			'yes' => 0,
			'maybe' => 0,
			'realno' => 0,
			'rank' => 0,
			'votes' => 0,
		];
	}

	/**
	 * Temporary fix
	 * Make sure, order is eqal to timestamp in date polls
	 */
	private function orderCorrection(int $timestamp, int $order): int {
		if ($timestamp) {
			return $timestamp;
		} else {
			return $order;
		}
	}
}
