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

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\Template\PublicTemplateResponse;

use OCA\Polls\Exceptions\NoUpdatesException;
use OCA\Polls\Db\Share;
use OCA\Polls\Db\Poll;
use OCA\Polls\Db\Comment;
use OCA\Polls\Model\Acl;
use OCA\Polls\Service\AnonymizeService;
use OCA\Polls\Service\CommentService;
use OCA\Polls\Service\MailService;
use OCA\Polls\Service\OptionService;
use OCA\Polls\Service\PollService;
use OCA\Polls\Service\ShareService;
use OCA\Polls\Service\SubscriptionService;
use OCA\Polls\Service\VoteService;
use OCA\Polls\Service\SystemService;
use OCA\Polls\Service\WatchService;

class PublicController extends Controller {

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var Acl */
	private $acl;

	/** @var CommentService */
	private $commentService;

	/** @var OptionService */
	private $optionService;

	/** @var MailService */
	private $mailService;

	/** @var PollService */
	private $pollService;

	/** @var Poll */
	private $poll;

	/** @var ShareService */
	private $shareService;

	/** @var Share */
	private $share;

	/** @var SubscriptionService */
	private $subscriptionService;

	/** @var SystemService */
	private $systemService;

	/** @var VoteService */
	private $voteService;

	/** @var WatchService */
	private $watchService;

	use ResponseHandle;

	public function __construct(
		string $appName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		Acl $acl,
		CommentService $commentService,
		MailService $mailService,
		OptionService $optionService,
		PollService $pollService,
		Poll $poll,
		ShareService $shareService,
		Share $share,
		SubscriptionService $subscriptionService,
		SystemService $systemService,
		VoteService $voteService,
		WatchService $watchService
	) {
		parent::__construct($appName, $request);
		$this->urlGenerator = $urlGenerator;
		$this->acl = $acl;
		$this->commentService = $commentService;
		$this->mailService = $mailService;
		$this->optionService = $optionService;
		$this->pollService = $pollService;
		$this->poll = $poll;
		$this->shareService = $shareService;
		$this->share = $share;
		$this->subscriptionService = $subscriptionService;
		$this->systemService = $systemService;
		$this->voteService = $voteService;
		$this->watchService = $watchService;
	}

	/**
	 * @PublicPage
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse|PublicTemplateResponse
	 */
	public function votePage() {
		if (\OC::$server->getUserSession()->isLoggedIn()) {
			return new TemplateResponse('polls', 'polls.tmpl', [
				'urlGenerator' => $this->urlGenerator]);
		} else {
			return new PublicTemplateResponse('polls', 'polls.tmpl', [
				'urlGenerator' => $this->urlGenerator]);
		}
	}

	/**
	 * get complete poll via token
	 * @PublicPage
	 * @NoAdminRequired
	 */
	public function getPoll(string $token): DataResponse {
		return $this->response(function () use ($token) {
			$this->acl->setToken($token);
			return [
				'acl' => $this->acl,
				'poll' => AnonymizeService::replaceUserId($this->acl->getPoll()),
			];
		});
	}

	/**
	 * Watch poll for updates
	 * @PublicPage
	 * @NoAdminRequired
	 */
	public function watchPoll(string $token, ?int $offset): DataResponse {
		$pollId = $this->acl->setToken($token)->getPollId();

		return $this->responseLong(function () use ($pollId, $offset) {
			$start = time();
			$timeout = 30;
			$offset = $offset ?? $start;

			while (empty($updates) && time() <= $start + $timeout) {
				sleep(1);
				$updates = $this->watchService->getUpdates($pollId, $offset);
			}
			if (empty($updates)) {
				throw new NoUpdatesException;
			}
			return ['updates' => $updates];
		});
	}

	/**
	 * Get share
	 * @PublicPage
	 * @NoAdminRequired
	 */
	public function getShare(string $token): DataResponse {
		return $this->response(function () use ($token) {
			return ['share' => $this->shareService->get($token, true)];
		});
	}

	/**
	 * Get Comments
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function getComments(string $token): DataResponse {
		return $this->response(function () use ($token) {
			return ['comments' => AnonymizeService::replaceUserId($this->commentService->list(0, $token))];
		});
	}

	/**
	 * Get votes
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function getVotes(string $token): DataResponse {
		return $this->response(function () use ($token) {
			return ['votes' => AnonymizeService::replaceUserId($this->voteService->list(0, $token))];
		});
	}

	/**
	 * Get options
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function getOptions(string $token): DataResponse {
		return $this->response(function () use ($token) {
			return ['options' => AnonymizeService::replaceUserId($this->optionService->list(0, $token))];
		});
	}

	/**
	 * Add options
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function addOption(string $token, int $timestamp = 0, string $pollOptionText = '', int $duration = 0): DataResponse {
		return $this->responseCreate(function () use ($token, $timestamp, $pollOptionText, $duration) {
			return ['option' => $this->optionService->add(0, $timestamp, $pollOptionText, $duration, $token)];
		});
	}

	/**
	 * Delete option
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function deleteOption(string $token, int $optionId): DataResponse {
		return $this->responseDeleteTolerant(function () use ($token, $optionId) {
			return ['option' => $this->optionService->delete($optionId, $token)];
		});
	}

	/**
	 * Get subscription status
	 * @PublicPage
	 * @NoAdminRequired
	 */
	public function getSubscription(string $token): DataResponse {
		return $this->response(function () use ($token) {
			return ['subscribed' => $this->subscriptionService->get(0, $token)];
		});
	}

	/**
	 * Set Vote
	 * @PublicPage
	 * @NoAdminRequired
	 */
	public function setVote(int $optionId, string $setTo, string $token): DataResponse {
		return $this->response(function () use ($optionId, $setTo, $token) {
			return ['vote' => $this->voteService->set($optionId, $setTo, $token)];
		});
	}

	/**
	 * Write a new comment to the db and returns the new comment as array
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function addComment(string $token, string $message): DataResponse {
		return $this->response(function () use ($token, $message) {
			return ['comment' => $this->commentService->add(0, $token, $message)];
		});
	}

	/**
	 * Delete Comment
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function deleteComment(int $commentId, string $token): DataResponse {
		return $this->responseDeleteTolerant(function () use ($commentId, $token) {
			return ['comment' => $this->commentService->delete($commentId, $token)];
		});
	}

	/**
	 * subscribe
	 * @PublicPage
	 * @NoAdminRequired
	 */
	public function subscribe(string $token): DataResponse {
		return $this->response(function () use ($token) {
			return ['subscribed' => $this->subscriptionService->set(true, 0, $token)];
		});
	}

	/**
	 * Unsubscribe
	 * @PublicPage
	 * @NoAdminRequired
	 */
	public function unsubscribe(string $token): DataResponse {
		return $this->response(function () use ($token) {
			return ['subscribed' => $this->subscriptionService->set(true, 0, $token)];
		});
	}

	/**
	 * Validate it the user name is reservrd
	 * return false, if this username already exists as a user or as
	 * a participant of the poll
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function validatePublicUsername(string $userName, string $token): DataResponse {
		try {
			return new DataResponse(['result' => $this->systemService->validatePublicUsername($userName, $token), 'name' => $userName], Http::STATUS_OK);
		} catch (\Exception $e) {
			return new DataResponse(['message' => $e->getMessage()], Http::STATUS_CONFLICT);
		}
	}

	/**
	 * Validate email address (simple validation)
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function validateEmailAddress(string $emailAddress): DataResponse {
		try {
			return new DataResponse(['result' => $this->systemService->validateEmailAddress($emailAddress), 'emailAddress' => $emailAddress], Http::STATUS_OK);
		} catch (\Exception $e) {
			return new DataResponse(['message' => $e->getMessage()], Http::STATUS_CONFLICT);
		}
	}

	/**
	 * Set EmailAddress
	 * @PublicPage
	 * @NoAdminRequired
	 */
	public function setEmailAddress(string $token, string $emailAddress = ''): DataResponse {
		return $this->response(function () use ($token, $emailAddress) {
			return ['share' => $this->shareService->setEmailAddress($token, $emailAddress, true)];
		});
	}

	/**
	 * Set EmailAddress
	 * @PublicPage
	 * @NoAdminRequired
	 */
	public function deleteEmailAddress(string $token): DataResponse {
		return $this->response(function () use ($token) {
			return ['share' => $this->shareService->deleteEmailAddress($token)];
		});
	}

	/**
	 * Create a personal share from a public share
	 * or update an email share with the username
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function register(string $token, string $userName, string $emailAddress = ''): DataResponse {
		return $this->responseCreate(function () use ($token, $userName, $emailAddress) {
			return ['share' => $this->shareService->register($token, $userName, $emailAddress)];
		});
	}

	/**
	 * Sent invitation mails for a share
	 * Additionally send notification via notifications
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function resendInvitation(string $token): DataResponse {
		return $this->response(function () use ($token) {
			return ['share' => $this->mailService->resendInvitation($token)];
		});
	}
}
