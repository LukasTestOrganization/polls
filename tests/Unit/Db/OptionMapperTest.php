<?php declare(strict_types=1);
/**
 * @copyright Copyright (c) 2017 Kai Schröer <git@schroeer.co>
 *
 * @author Kai Schröer <git@schroeer.co>
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

namespace OCA\Polls\Tests\Unit\Db;

use OCA\Polls\Db\Poll;
use OCA\Polls\Db\PollMapper;
use OCA\Polls\Db\Option;
use OCA\Polls\Db\OptionMapper;
use OCA\Polls\Tests\Unit\UnitTestCase;
use OCP\IDBConnection;
use League\FactoryMuffin\Faker\Facade as Faker;

class OptionMapperTest extends UnitTestCase {

	/** @var IDBConnection */
	private $con;
	/** @var OptionMapper */
	private $optionMapper;
	/** @var PollMapper */
	private $pollMapper;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->con = \OC::$server->getDatabaseConnection();
		$this->optionMapper = new OptionMapper($this->con);
		$this->pollMapper = new PollMapper($this->con);
	}

	/**
	 * Create some fake data and persist them to the database.
	 *
	 * @return Option
	 */
	public function testCreate() {
		/** @var Poll $poll */
		$poll = $this->fm->instance('OCA\Polls\Db\Poll');
		$this->assertInstanceOf(Poll::class, $this->pollMapper->insert($poll));

		/** @var Option $option */
		$option = $this->fm->instance('OCA\Polls\Db\Option');
		$option->setPollId($poll->getId());
		$this->assertInstanceOf(Option::class, $this->optionMapper->insert($option));

		return $option;
	}

	/**
	 * Update the previously created entry and persist the changes.
	 *
	 * @depends testCreate
	 * @param Option $option
	 * @return Option
	 */
	public function testUpdate(Option $option) {
		$newPollOptionText = Faker::text(255);
		$option->setPollOptionText($newPollOptionText());
		$this->assertInstanceOf(Option::class, $this->optionMapper->update($option));

		return $option;
	}

	/**
	 * Delete the previously created entries from the database.
	 *
	 * @depends testUpdate
	 * @param Option $option
	 */
	public function testDelete(Option $option) {
		$poll = $this->pollMapper->find($option->getPollId());
		$this->optionMapper->delete($option);
		$this->pollMapper->delete($poll);
	}
}
