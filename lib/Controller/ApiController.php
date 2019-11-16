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

namespace OCA\Polls\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Db\DoesNotExistException;

use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Security\ISecureRandom;

use OCA\Polls\Model\Acl;
use OCA\Polls\Db\Event;
use OCA\Polls\Db\EventMapper;
use OCA\Polls\Db\Option;
use OCA\Polls\Db\OptionMapper;
use OCA\Polls\Db\Vote;
use OCA\Polls\Db\VoteMapper;
use OCA\Polls\Db\Comment;
use OCA\Polls\Db\CommentMapper;
use OCA\Polls\Db\Notification;
use OCA\Polls\Db\NotificationMapper;

use OCA\Polls\Controller\CommentController;
use OCA\Polls\Controller\EventController;
use OCA\Polls\Controller\NotificationController;
use OCA\Polls\Controller\OptionController;
use OCA\Polls\Controller\VoteController;

use OCA\Polls\Service\EventService;


class ApiController extends Controller {

	private $userId;
	private $groupManager;
	private $userManager;
	private $eventMapper;
	private $eventService;
	private $optionMapper;
	private $voteMapper;
	private $commentMapper;
	private $commentController;
	private $eventController;
	private $notificationController;
	private $optionController;
	private $voteController;
	private $acl;

	/**
	 * PageController constructor.
	 * @param string $appName
	 * @param $UserId
	 * @param IGroupManager $groupManager
	 * @param IRequest $request
	 * @param IUserManager $userManager
	 * @param EventMapper $eventMapper
	 * @param OptionMapper $optionMapper
	 * @param VoteMapper $voteMapper
	 * @param CommentMapper $commentMapper
	 * @param CommentController $commentController
	 * @param EventController $eventController
	 * @param NotificationController $notificationController
	 * @param OptionController $optionController
	 * @param VoteController $voteController
	 * @param EventService $eventService
	 * @param Acl $acl
	 */
	public function __construct(
		$appName,
		$UserId,
		IGroupManager $groupManager,
		IRequest $request,
		IUserManager $userManager,
		EventMapper $eventMapper,
		OptionMapper $optionMapper,
		VoteMapper $voteMapper,
		CommentMapper $commentMapper,
		CommentController $commentController,
		EventController $eventController,
		NotificationController $notificationController,
		OptionController $optionController,
		VoteController $voteController,
		EventService $eventService,
		Acl $acl
	) {
		parent::__construct($appName, $request);
		$this->userId = $UserId;
		$this->groupManager = $groupManager;
		$this->userManager = $userManager;
		$this->eventMapper = $eventMapper;
		$this->optionMapper = $optionMapper;
		$this->voteMapper = $voteMapper;
		$this->commentMapper = $commentMapper;
		$this->commentController = $commentController;
		$this->eventController = $eventController;
		$this->notificationController = $notificationController;
		$this->optionController = $optionController;
		$this->voteController = $voteController;
		$this->eventService = $eventService;
		$this->acl = $acl;
	}

	/**
	 * Transforms a string with user and group names to an array
	 * of nextcloud users and groups
	 * @param string $item
	 * @return array
	 */
	private function convertAccessList($item) {
		$split = array();
		if (strpos($item, 'user_') === 0) {
			$user = $this->userManager->get(substr($item, 5));
			$split = [
				'id' => $user->getUID(),
				'user' => $user->getUID(),
				'type' => 'user',
				'desc' => 'user',
				'icon' => 'icon-user',
				'displayName' => $user->getDisplayName(),
				'avatarURL' => '',
				'lastLogin' => $user->getLastLogin(),
				'cloudId' => $user->getCloudId()
			];
		} elseif (strpos($item, 'group_') === 0) {
			$group = substr($item, 6);
			$group = $this->groupManager->get($group);
			$split = [
				'id' => $group->getGID(),
				'user' => $group->getGID(),
				'type' => 'group',
				'desc' => 'group',
				'icon' => 'icon-group',
				'displayName' => $group->getDisplayName(),
				'avatarURL' => ''
			];
		}

		return($split);
	}


	/**
	 * Read all shares (users and groups with access) of a poll based on the poll id
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param integer $pollId
	 * @return array
	 */
	public function getAclByToken($token) {
		$acl = $this->acl->setToken($token);
		return new DataResponse($acl, Http::STATUS_OK);

	}

	/**
	 * Read all shares (users and groups with access) of a poll based on the poll id
	 * @NoAdminRequired
	 * @PublicPage
	 * @NoCSRFRequired
	 * @param integer $pollId
	 * @return array
	 */
	public function getAclById($id) {
		$acl = $this->acl->setPollId($id);
		// $acl = $this->acl->setUserId('dartcafe');
		return new DataResponse($acl, Http::STATUS_OK);
	}

	/**
	 * Read all shares (users and groups with access) of a poll based on the poll id
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param integer $pollId
	 * @return array
	 */
	public function getShares($pollId) {

		$accessList = array();

		try {
			$poll = $this->eventMapper->find($pollId);
			if (!strpos('|public|hidden|registered', $poll->getAccess())) {
				$accessList = explode(';', $poll->getAccess());
				$accessList = array_filter($accessList);
				$accessList = array_map(array($this, 'convertAccessList'), $accessList);
			}
		} catch (DoesNotExistException $e) {
			// return silently
		} finally {
			return $accessList;
		}

	}

	/**
	 * Get all polls
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @deprecated 1.0
	 * @return DataResponse
	 */

	public function getPolls() {
		if (!\OC::$server->getUserSession()->isLoggedIn()) {
			return new DataResponse(null, Http::STATUS_UNAUTHORIZED);
		}

		try {
			$events = $this->eventMapper->findAll();
		} catch (DoesNotExistException $e) {
			return new DataResponse($e, Http::STATUS_NOT_FOUND);
		}
		$polls = array();

		foreach ($events as &$event) {
			if ($this->eventService->grantAccessAs($event->id) !== "none") {
				$polls[] = (object) [
					 'id' => $event->id,
					 'event' => $this->eventMapper->find($event->id),
					 'options' => $this->optionMapper->findByPoll($event->id),
					 'votes' => $this->voteMapper->findByPoll($event->id),
					 'comments' => $this->commentMapper->findByPoll($event->id)
				 ];
			}
		}

		return new DataResponse($polls, Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @param int $pollId
	 * @return DataResponse
	 */
	public function removePoll($id) {
		try {
			$pollToDelete = $this->eventMapper->find($id);
		} catch (DoesNotExistException $e) {
			return new DataResponse($e, Http::STATUS_NOT_FOUND);
		}

		if ($this->userId !== $pollToDelete->getOwner() && !$this->groupManager->isAdmin($this->userId)) {
			return new DataResponse(null, Http::STATUS_UNAUTHORIZED);
		}

		$this->commentMapper->deleteByPoll($id);
		$this->voteMapper->deleteByPoll($id);
		$this->optionMapper->deleteByPoll($id);
		// $this->notificationMapper->deleteByPoll($id);
		$this->eventMapper->delete($pollToDelete);
		return new DataResponse(array(
			'id' => $id,
			'action' => 'deleted'
		), Http::STATUS_OK);
	}

	/**
	 * Write poll (create/update)
	 * @NoAdminRequired
	 * @param Array $event
	 * @param Array $options
	 * @param Array  $shares
	 * @param string $mode
	 * @return DataResponse
	 */
	public function writePoll($event, $voteOptions, $shares, $mode) {
		if (!\OC::$server->getUserSession()->isLoggedIn()) {
			return new DataResponse(null, Http::STATUS_UNAUTHORIZED);
		} else {
			$currentUser = \OC::$server->getUserSession()->getUser()->getUID();
			$AdminAccess = $this->groupManager->isAdmin($currentUser);
		}

		$newEvent = new Event();

		// Set the configuration options entered by the user
		$newEvent->setTitle($event['title']);
		$newEvent->setDescription($event['description']);

		$newEvent->setType($event['type']);
		$newEvent->setIsAnonymous($event['isAnonymous']);
		$newEvent->setFullAnonymous($event['fullAnonymous']);
		$newEvent->setAllowMaybe($event['allowMaybe']);

		if ($event['access'] === 'select') {
			$shareAccess = '';
			foreach ($shares as $shareElement) {
				if ($shareElement['type'] === 'user') {
					$shareAccess = $shareAccess . 'user_' . $shareElement['id'] . ';';
				} elseif ($shareElement['type'] === 'group') {
					$shareAccess = $shareAccess . 'group_' . $shareElement['id'] . ';';
				}
			}
			$newEvent->setAccess(rtrim($shareAccess, ';'));
		} else {
			$newEvent->setAccess($event['access']);
		}

		if ($event['expiration']) {
			$newEvent->setExpire(date('Y-m-d H:i:s', strtotime($event['expirationDate'])));
		} else {
			$newEvent->setExpire(null);
		}

		if ($event['type'] === 'datePoll') {
			$newEvent->setType(0);
		} elseif ($event['type'] === 'textPoll') {
			$newEvent->setType(1);
		}

		if ($mode === 'edit') {
			// Edit existing poll
			$oldPoll = $this->eventMapper->find($event['id']);

			// Check if current user is allowed to edit existing poll
			if ($oldPoll->getOwner() !== $currentUser && !$AdminAccess) {
				// If current user is not owner of existing poll deny access
				return new DataResponse(null, Http::STATUS_UNAUTHORIZED);
			}

			// else take owner, id of existing poll
			$newEvent->setOwner($oldPoll->getOwner());
			$newEvent->setId($oldPoll->getId());
			$this->eventMapper->update($newEvent);
			$this->optionMapper->deleteByPoll($newEvent->getId());

		} elseif ($mode === 'create') {
			// Create new poll
			// Define current user as owner, set new creation date
			$newEvent->setOwner($currentUser);
			$newEvent->setCreated(date('Y-m-d H:i:s'));
			$newEvent = $this->eventMapper->insert($newEvent);
		}

		// Update options
		foreach ($voteOptions as $optionElement) {
			$newOption = new Option();

			$newOption->setPollId($newEvent->getId());
			$newOption->setpollOptionText(trim(htmlspecialchars($optionElement['text'])));
			$newOption->setTimestamp($optionElement['timestamp']);

			$this->optionMapper->insert($newOption);
		}

		return new DataResponse(array(
			'id' => $newEvent->getId()
		), Http::STATUS_OK);

	}
}
