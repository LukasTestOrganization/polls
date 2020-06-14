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

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;

use OCP\IRequest;
use \OCP\IURLGenerator;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\Polls\Exceptions\NotAuthorizedException;

use OCA\Polls\Service\CommentService;



class CommentApiController extends ApiController {

	private $optionService;
	private $urlGenerator;
	/**
	 * CommentApiController constructor.
	 * @param string $appName
	 * @param IRequest $request
	 * @param CommentService $commentService
	 */

	public function __construct(
		string $appName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		CommentService $commentService
	) {
		parent::__construct($appName,
			$request,
			'POST, GET, DELETE',
            'Authorization, Content-Type, Accept',
            1728000);
		$this->commentService = $commentService;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * get
	 * Read all comments of a poll based on the poll id and return list as array
	 * @NoAdminRequired
	 * @CORS
	 * @PublicPage
	 * @NoCSRFRequired
	 * @param integer $pollId
	 * @return DataResponse
	 */
	public function list($pollId, $token = '') {
		try {
			return new DataResponse($this->commentService->list($pollId, $token), Http::STATUS_OK);
		} catch (NotAuthorizedException $e) {
			return new DataResponse('Unauthorized', Http::STATUS_FORBIDDEN);
		} catch (DoesNotExistException $e) {
			return new DataResponse('Poll with id ' . $pollId . ' not found', Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Read all comments of a poll based on a share token and return list as array
	 * @NoAdminRequired
	 * @CORS
	 * @NoCSRFRequired
	 * @PublicPage
	 * @param string $token
	 * @return DataResponse
	 */
	public function getByToken($token) {
		try {
			return new DataResponse($this->commentService->get(0, $token), Http::STATUS_OK);
		} catch (NotAuthorizedException $e) {
			return new DataResponse('Unauthorized', Http::STATUS_FORBIDDEN);
		} catch (DoesNotExistException $e) {
			return new DataResponse('Poll with token ' . $token . ' not found', Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Write a new comment to the db and returns the new comment as array
	 * @NoAdminRequired
	 * @CORS
	 * @NoCSRFRequired
	 * @PublicPage
	 * @param int $pollId
	 * @param string $message
	 * @param string $token
	 * @return DataResponse
	 */
	public function add($message, $pollId, $token) {
		try {
			return new DataResponse($this->commentService->add($message, $pollId, $token), Http::STATUS_CREATED);
		} catch (NotAuthorizedException $e) {
			return new DataResponse('Unauthorized', Http::STATUS_FORBIDDEN);
		} catch (DoesNotExistException $e) {
			return new DataResponse('Poll with id ' . $pollId . ' not found', Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Delete Comment
	 * @NoAdminRequired
	 * @CORS
	 * @NoCSRFRequired
	 * @PublicPage
	 * @param int $commentId
	 * @param string $token
	 * @return DataResponse
	 */
	public function delete($commentId, $token) {
		try {
			$this->commentService->delete($commentId, $token);
			return new DataResponse($commentId, Http::STATUS_OK);
		} catch (NotAuthorizedException $e) {
			return new DataResponse('Unauthorized', Http::STATUS_FORBIDDEN);
		} catch (DoesNotExistException $e) {
			return new DataResponse('Comment does not exist', Http::STATUS_NOT_FOUND);
		}
	}

}