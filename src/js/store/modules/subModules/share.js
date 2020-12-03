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

const defaultShares = () => {
	return {
		displayName: '',
		id: null,
		invitationSent: 0,
		pollId: null,
		token: '',
		type: '',
		emailAddress: '',
		userId: '',
	}
}

const state = defaultShares()

const namespaced = true

const mutations = {
	set(state, payload) {
		Object.assign(state, payload.share)
	},

	reset(state) {
		Object.assign(state, defaultShares())
	},

}

const actions = {
	get(context, payload) {
		const endPoint = 'apps/polls/share'
		return axios.get(generateUrl(endPoint + '/' + payload.token))
			.then((response) => {
				context.commit('set', { share: response.data.share })
				return response.data
			})
			.catch((error) => {
				console.error('Error retrieving share', { error: error.response }, { payload: payload })
				throw error
			})
	},

	sendInvitation(context, payload) {
		const endPoint = 'apps/polls/share'
		return axios.post(generateUrl(endPoint + '/' + context.state.token + '/invite'))
			.then((response) => {
				context.commit('set', { share: response.data.share })
				return response
			})
			.catch((error) => {
				console.error('Error sending invitation', { error: error.response }, { payload: payload })
				throw error
			})
	},
}

export default { namespaced, state, mutations, actions }
