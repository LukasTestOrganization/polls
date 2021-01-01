<?php
/**
 * @copyright Copyright (c) 2017 Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 *
 * @author Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
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

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

/**
 * @template-extends QBMapper<Share>
 */
class ShareMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'polls_share', '\OCA\Polls\Db\Share');
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @return Share[]
	 */
	public function findAll() {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from($this->getTableName());

		return $this->findEntities($qb);
	}


	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @return Share[]
	 */
	public function findByPoll(int $pollId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from($this->getTableName())
		   ->where(
			   $qb->expr()->eq('poll_id', $qb->createNamedParameter($pollId, IQueryBuilder::PARAM_INT))
		   );

		return $this->findEntities($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @return Share
	 */
	public function findByPollAndUser(int $pollId, string $userId): Share {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from($this->getTableName())
		   ->where(
			   $qb->expr()->eq('poll_id', $qb->createNamedParameter($pollId, IQueryBuilder::PARAM_INT))
		   )
		   ->andWhere(
			   $qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
		   );

		return $this->findEntity($qb);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @return Share
	 */
	public function findByToken(string $token) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
		   ->from($this->getTableName())
		   ->where(
			   $qb->expr()->eq('token', $qb->createNamedParameter($token, IQueryBuilder::PARAM_STR))
		   );

		return $this->findEntity($qb);
	}

	/**
	 * @return void
	 */
	public function deleteByPoll($pollId): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
		   ->where(
			   $qb->expr()->eq('poll_id', $qb->createNamedParameter($pollId, IQueryBuilder::PARAM_INT))
		   );

		$qb->execute();
	}

	/**
	 * @return void
	 */
	public function remove($shareId): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
		   ->where(
			   $qb->expr()->eq('id', $qb->createNamedParameter($shareId, IQueryBuilder::PARAM_INT))
		   );

		$qb->execute();
	}

	/**
	 * @return void
	 */
	public function removeDuplicates() {
		$query = $this->db->getQueryBuilder();

		// make sure, all public shares fit to the unique index added in schemaChange(),
		// by copying token to user_id
		$query->update($this->getTableName())
			->set('user_id', 'token')
			->where('type = :type')
			->setParameter('type', 'public')
			->execute();

		// remove duplicates from oc_polls_share
		// preserve the first entry
		$query = $this->db->getQueryBuilder();
		$query->select('id', 'type', 'poll_id', 'user_id')
			->from($this->getTableName());
		$foundEntries = $query->execute();

		$delete = $this->db->getQueryBuilder();
		$delete->delete($this->getTableName())->where('id = :id');

		$entries2Keep = [];

		while ($row = $foundEntries->fetch()) {
			$currentRecord = [
				$row['poll_id'],
				$row['type'],
				$row['user_id']
			];

			if (in_array($currentRecord, $entries2Keep)) {
				$delete->setParameter('id', $row['id']);
				$delete->execute();
			} else {
				$entries2Keep[] = $currentRecord;
			}
		}
	}
}
