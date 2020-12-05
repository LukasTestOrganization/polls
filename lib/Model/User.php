<?php
/**
 * @copyright Copyright (c) 2017 Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 *
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


namespace OCA\Polls\Model;

class User extends UserGroupClass {
	public const TYPE = 'user';
	public const ICON = 'icon-user';

	private $user;

	public function __construct(
		$id
	) {
		parent::__construct($id, self::TYPE);
		$this->icon = self::ICON;
		$this->isNoUser = false;
		$this->description = \OC::$server->getL10N('polls')->t('User');

		$this->user = \OC::$server->getUserManager()->get($this->id);
		$this->displayName = $this->user->getDisplayName();
		$this->emailAddress = $this->user->getEMailAddress();
		$this->language = \OC::$server->getConfig()->getUserValue($this->id, 'core', 'lang');
	}

	public function getUserIsDisabled() {
		return !\OC::$server->getUserManager()->get($this->id)->isEnabled();
	}

	public static function listRaw(string $query = '') {
		return \OC::$server->getUserManager()->search($query);
	}

	/**
	 * @return array
	 *
	 * @psalm-return list<mixed>
	 */
	public static function search(string $query = '', array $skip = []) {
		$users = [];
		foreach (self::listRaw($query) as $user) {
			if (!in_array($user->getUID(), $skip)) {
				$users[] = new Self($user->getUID());
			}
		}
		return $users;
	}
}
