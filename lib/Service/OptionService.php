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

namespace OCA\Polls\Service;

use DateTime;
use OCP\AppFramework\Db\DoesNotExistException;
use OCA\Polls\Exceptions\NotAuthorizedException;
use OCA\Polls\Exceptions\BadRequestException;
use OCA\Polls\Exceptions\DuplicateEntryException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use OCA\Polls\Db\OptionMapper;
use OCA\Polls\Db\Option;
use OCA\Polls\Db\PollMapper;
use OCA\Polls\Db\Poll;
use OCA\Polls\Db\Watch;
use OCA\Polls\Model\Acl;

class OptionService {

	/** @var Acl */
	private $acl;

	/** @var Option */
	private $option;

	/** @var OptionMapper */
	private $optionMapper;

	/** @var PollMapper */
	private $pollMapper;

	/** @var WatchService */
	private $watchService;

	public function __construct(
		Acl $acl,
		Option $option,
		OptionMapper $optionMapper,
		PollMapper $pollMapper,
		WatchService $watchService
	) {
		$this->acl = $acl;
		$this->option = $option;
		$this->optionMapper = $optionMapper;
		$this->pollMapper = $pollMapper;
		$this->watchService = $watchService;
	}

	/**
	 * 	 * Get all options of given poll
	 *
	 * @return Option[]
	 *
	 * @psalm-return array<array-key, Option>
	 */
	public function list(?int $pollId = 0, string $token = ''): array {
		if ($token) {
			$this->acl->setToken($token);
		} else {
			$this->acl->setPollId($pollId)->request(Acl::PERMISSION_VIEW);
		}

		if (!$this->acl->isAllowed(Acl::PERMISSION_VIEW)) {
			throw new NotAuthorizedException;
		}

		try {
			return $this->optionMapper->findByPoll($this->acl->getPollId());
		} catch (DoesNotExistException $e) {
			return [];
		}
	}

	/**
	 * 	 * Get option
	 *
	 * @return Option
	 */
	public function get(int $optionId): Option {
		$this->acl->setPollId($this->optionMapper->find($optionId)->getPollId())->request(Acl::PERMISSION_VIEW);

		if (!$this->acl->isAllowed(Acl::PERMISSION_VIEW)) {
			throw new NotAuthorizedException;
		}

		return $this->optionMapper->find($optionId);
	}


	/**
	 * 	 * Add a new option
	 *
	 * @return Option
	 */
	public function add(int $pollId, int $timestamp = 0, string $pollOptionText = ''): Option {
		$this->acl->setPollId($pollId)->request(Acl::PERMISSION_EDIT);
		$this->option = new Option();
		$this->option->setPollId($pollId);
		$this->option->setOrder($this->getHighestOrder($this->option->getPollId()) + 1);
		$this->setOption($timestamp, $pollOptionText);

		try {
			$this->option = $this->optionMapper->insert($this->option);
			$this->watchService->writeUpdate($this->option->getPollId(), Watch::OBJECT_OPTIONS);
		} catch (UniqueConstraintViolationException $e) {
			throw new DuplicateEntryException('This option already exists');
		}
		return $this->option;
	}

	/**
	 * 	 * Update option
	 *
	 * @return Option
	 */
	public function update(int $optionId, int $timestamp = 0, ?string $pollOptionText = ''): Option {
		$this->option = $this->optionMapper->find($optionId);
		$this->acl->setPollId($this->option->getPollId())->request(Acl::PERMISSION_EDIT);
		$this->setOption($timestamp, $pollOptionText);

		$this->option = $this->optionMapper->update($this->option);
		$this->watchService->writeUpdate($this->option->getPollId(), Watch::OBJECT_OPTIONS);
		return $this->option;
	}

	/**
	 * 	 * Delete option
	 *
	 * @return Option
	 */
	public function delete(int $optionId): Option {
		$this->option = $this->optionMapper->find($optionId);
		$this->acl->setPollId($this->option->getPollId())->request(Acl::PERMISSION_EDIT);
		$this->optionMapper->delete($this->option);
		$this->watchService->writeUpdate($this->option->getPollId(), Watch::OBJECT_OPTIONS);

		return $this->option;
	}

	/**
	 * 	 * Switch optoin confirmation
	 *
	 * @return Option
	 */
	public function confirm(int $optionId): Option {
		$this->option = $this->optionMapper->find($optionId);
		$this->acl->setPollId($this->option->getPollId())->request(Acl::PERMISSION_EDIT);

		if ($this->option->getConfirmed()) {
			$this->option->setConfirmed(0);
		} else {
			$this->option->setConfirmed(time());
		}

		$this->option = $this->optionMapper->update($this->option);
		$this->watchService->writeUpdate($this->option->getPollId(), Watch::OBJECT_OPTIONS);
		return $this->option;
	}

	/**
	 * 	 * Make a sequence of date poll options
	 *
	 * @return Option[]
	 *
	 * @psalm-return array<array-key, Option>
	 */
	public function sequence(int $optionId, int $step, string $unit, int $amount): array {
		$baseDate = new DateTime;
		$this->option = $this->optionMapper->find($optionId);
		$this->acl->setPollId($this->option->getPollId())->request(Acl::PERMISSION_EDIT);

		if ($step === 0) {
			return $this->optionMapper->findByPoll($this->option->getPollId());
		}

		$baseDate->setTimestamp($this->option->getTimestamp());

		for ($i = 0; $i < $amount; $i++) {
			$clonedOption = new Option();
			$clonedOption->setPollId($this->option->getPollId());
			$clonedOption->setConfirmed(0);
			$clonedOption->setTimestamp($baseDate->modify($step . ' ' . $unit)->getTimestamp());
			$clonedOption->setOrder($clonedOption->getTimestamp());
			$clonedOption->setPollOptionText($baseDate->format('c'));
			try {
				$this->optionMapper->insert($clonedOption);
			} catch (UniqueConstraintViolationException $e) {
				\OC::$server->getLogger()->warning('skip adding ' . $baseDate->format('c') . 'for pollId' . $this->option->getPollId() . '. Option alredy exists.');
			}
		}
		$this->watchService->writeUpdate($this->option->getPollId(), Watch::OBJECT_OPTIONS);
		return $this->optionMapper->findByPoll($this->option->getPollId());
	}

	/**
	 * 	 * Copy options from $fromPoll to $toPoll
	 *
	 * @return Option[]
	 *
	 * @psalm-return array<array-key, Option>
	 */
	public function clone(int $fromPollId, int $toPollId): array {
		$this->acl->setPollId($fromPollId);

		foreach ($this->optionMapper->findByPoll($fromPollId) as $origin) {
			$option = new Option();
			$option->setPollId($toPollId);
			$option->setConfirmed(0);
			$option->setPollOptionText($origin->getPollOptionText());
			$option->setTimestamp($origin->getTimestamp());
			$option->setOrder($origin->getOrder());
			$this->optionMapper->insert($option);
		}

		return $this->optionMapper->findByPoll($toPollId);
	}

	/**
	 * 	 * Reorder options with the order specified by $options
	 *
	 * @return Option[]
	 *
	 * @psalm-return array<array-key, Option>
	 */
	public function reorder(int $pollId, array $options): array {
		try {
			$poll = $this->pollMapper->find($pollId);
			$this->acl->setPoll($poll)->request(Acl::PERMISSION_EDIT);

			if ($poll->getType() === Poll::TYPE_DATE) {
				throw new BadRequestException("Not allowed in date polls");
			}
		} catch (DoesNotExistException $e) {
			throw new NotAuthorizedException;
		}

		$i = 0;
		foreach ($options as $option) {
			$this->option = $this->optionMapper->find($option['id']);
			if ($pollId === intval($this->option->getPollId())) {
				$this->option->setOrder(++$i);
				$this->optionMapper->update($this->option);
			}
		}

		$this->watchService->writeUpdate($pollId, Watch::OBJECT_OPTIONS);
		return $this->optionMapper->findByPoll($pollId);
	}

	/**
	 * 	 * Change order for $optionId and reorder the options
	 *
	 * @NoAdminRequired
	 *
	 * @return Option[]
	 *
	 * @psalm-return array<array-key, Option>
	 */
	public function setOrder(int $optionId, int $newOrder): array {
		try {
			$this->option = $this->optionMapper->find($optionId);
			$poll = $this->pollMapper->find($this->option->getPollId());
			$this->acl->setPoll($poll)->request(Acl::PERMISSION_EDIT);

			if ($poll->getType() === Poll::TYPE_DATE) {
				throw new BadRequestException("Not allowed in date polls");
			}
		} catch (DoesNotExistException $e) {
			throw new NotAuthorizedException;
		}

		if ($newOrder < 1) {
			$newOrder = 1;
		} elseif ($newOrder > $this->getHighestOrder($poll->getId())) {
			$newOrder = $this->getHighestOrder($poll->getId());
		}

		foreach ($this->optionMapper->findByPoll($poll->getId()) as $option) {
			$option->setOrder($this->moveModifier($this->option->getOrder(), $newOrder, $option->getOrder()));
			$this->optionMapper->update($option);
		}

		$this->watchService->writeUpdate($this->option->getPollId(), Watch::OBJECT_OPTIONS);
		return $this->optionMapper->findByPoll($this->option->getPollId());
	}

	/**
	 * 	 * moveModifier - evaluate new order
	 * 	 * depending on the old and the new position of a moved array item
	 * 	 * $moveFrom - old position of the moved item
	 * 	 * $moveTo   - target posotion of the moved item
	 * 	 * $value    - current position of the current item
	 * 	 * Returns the modified new new position of the current item
	 *
	 * @return int
	 */
	private function moveModifier(int $moveFrom, int $moveTo, int $currentPosition): int {
		$moveModifier = 0;
		if ($moveFrom < $currentPosition && $currentPosition <= $moveTo) {
			// moving forward
			$moveModifier = -1;
		} elseif ($moveTo <= $currentPosition && $currentPosition < $moveFrom) {
			//moving backwards
			$moveModifier = 1;
		} elseif ($moveFrom === $currentPosition) {
			return $moveTo;
		}
		return $currentPosition + $moveModifier;
	}

	/**
	 * Set option entities validated
	 */
	private function setOption(int $timestamp = 0, ?string $pollOptionText = ''): void {
		$poll = $this->pollMapper->find($this->option->getPollId());

		if ($poll->getType() === Poll::TYPE_DATE) {
			$this->option->setTimestamp($timestamp);
			$this->option->setOrder($timestamp);
			$this->option->setPollOptionText(date('c', $timestamp));
		} else {
			$this->option->setPollOptionText($pollOptionText);
		}
	}

	/**
	 * 	 * Get the highest order number in $pollId
	 * 	 * Return Highest order number
	 *
	 * @return int
	 */
	private function getHighestOrder(int $pollId): int {
		$highestOrder = 0;
		foreach ($this->optionMapper->findByPoll($pollId) as $option) {
			if ($option->getOrder() > $highestOrder) {
				$highestOrder = $option->getOrder();
			}
		}
		return $highestOrder;
	}
}
