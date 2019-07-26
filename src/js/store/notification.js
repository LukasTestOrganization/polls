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

import axios from 'nextcloud-axios'

const defaultNotification = () => {
	return {
		subscribed: false,
	}
}

const state = defaultNotification()

const mutations = {

	setNotification(state, payload) {
		state.subscribed = payload
	},

	// changeNotification(state) {
	// 	state.subscribed = !state.subscribed
	// },

}

const actions = {
	getSubscription({ commit }, payload) {
		console.log(payload)
		axios.get(OC.generateUrl('apps/polls/get/notification/' + payload))
			.then((response) => {
				// console.log(response.data)
				commit('setNotification', true)
			}, (error) => {
				/* eslint-disable-next-line no-console */
				console.log(error.response)
				commit('setNotification', false)
			})
	},

	writeSubscriptionPromise({ commit }, payload) {
		console.log(state.currentUser)
		console.log(state)
		console.log(payload)
		if (state.currentUser !== '') {
			return axios.post(OC.generateUrl('apps/polls/set/notification'), { pollId: payload.pollId, subscribed: state.subscribed})
				.then((response) => {
				}, (error) => {
					/* eslint-disable-next-line no-console */
					console.log(error.response)
				})
		}
	}
}

export default { state, mutations, actions }
