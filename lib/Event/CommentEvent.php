<?php
/*
 * @copyright Copyright (c) 2021 René Gieling <github@dartcafe.de>
 *
 * @author René Gieling <github@dartcafe.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Polls\Event;

use OCP\EventDispatcher\Event;
use OCA\Polls\Db\Comment;

class CommentEvent extends Event {
	private $comment;

	public function __construct(Comment $comment) {
		parent::__construct();
		$this->comment = $comment;
	}

	public function getComment(): Comment {
		return $this->comment;
	}

	public function getPollId(): int {
		return $this->comment->getPollId();
	}

	public function getLogMsg(): string {
		return '';
	}

	public function getActor(): string {
		if (\OC::$server->getUserSession()->isLoggedIn()) {
			return \OC::$server->getUserSession()->getUser()->getUID();
		}
		return $this->comment->getUserId();
	}
}
