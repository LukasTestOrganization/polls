/*
 * @copyright Copyright (c) 2019 Rene Gieling <github@dartcafe.de>
 *
 * @author Rene Gieling <github@dartcafe.de>
 * @author Julius Härtl <jus@bitgrid.net>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const defaultSubscription = () => {
	return {
		subscribed: false,
	}
}

const state = defaultSubscription()

const mutations = {

	setSubscription(state, payload) {
		state.subscribed = payload
	},

}

const actions = {

	getSubscription(context) {
		let endPoint = 'apps/polls'
		if (context.rootState.poll.acl.token) {
			endPoint = endPoint + '/s/' + context.rootState.poll.acl.token
		} else {
			endPoint = endPoint + '/poll/' + context.rootState.poll.id
		}

		return axios.get(generateUrl(endPoint + '/subscription'))
			.then((response) => {
				context.commit('setSubscription', response.data.subscribed)
			})
			.catch(() => {
				context.commit('setSubscription', false)
			})
	},

	writeSubscription(context) {
		let endPoint = 'apps/polls'
		if (context.rootState.poll.acl.token) {
			endPoint = endPoint + '/s/' + context.rootState.poll.acl.token
		} else {
			endPoint = endPoint + '/poll/' + context.rootState.poll.id
		}
		if (state.subscribed) {
			endPoint = endPoint + '/subscribe'
		} else {
			endPoint = endPoint + '/unsubscribe'
		}

		return axios.put(generateUrl(endPoint))
			.then(() => {
			})
			.catch((error) => {
				console.error(error.response)
			})
	},
}

export default { state, mutations, actions }
