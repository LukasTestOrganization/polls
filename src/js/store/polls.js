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

const state = {
	list: []
}

const mutations = {
	setPolls(state, { list }) {
		state.list = list
	}
}

const getters = {
	countPolls: (state) => {
		return state.list.length
	},
	myPolls: (state) => {
		return state.list.filter(poll => (poll.event.owner === OC.currentUser))
	},
	invitationPolls: (state) => {
		return state.list.filter(poll => (poll.grantedAs === 'userInvitation'))
	},
	publicPolls: (state) => {
		return state.list.filter(poll => (poll.event.access === 'public'))
	},
	hiddenPolls: (state) => {
		return state.list.filter(poll => (poll.event.access === 'hidden'))
	}
}

const actions = {
	loadPolls({ commit }) {
		return axios.get(OC.generateUrl('apps/polls/get/polls'))
			.then((response) => {
				commit('setPolls', { list: response.data })
			}, (error) => {
			/* eslint-disable-next-line no-console */
				console.log(error.response)
			})
	},

	deletePollPromise(context, payload) {
		return axios.post(
			OC.generateUrl('apps/polls/remove/poll'),
			payload.event
		)
	}
}

export default { state, mutations, getters, actions }
