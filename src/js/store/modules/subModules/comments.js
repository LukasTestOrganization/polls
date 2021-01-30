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

const defaultComments = () => {
	return {
		list: [],
	}
}

const state = defaultComments()

const namespaced = true

const mutations = {

	set(state, payload) {
		state.list = payload.comments
	},

	reset(state) {
		Object.assign(state, defaultComments())
	},

	add(state, payload) {
		state.list.push(payload.comment)
	},

	delete(state, payload) {
		state.list = state.list.filter(comment => {
			return comment.id !== payload.comment.id
		})
	},
}

const getters = {
	count: state => {
		return state.list.length
	},
}

const actions = {
	list(context) {
		let endPoint = 'apps/polls'

		if (context.rootState.route.name === 'publicVote') {
			endPoint = endPoint + '/s/' + context.rootState.route.params.token
		} else if (context.rootState.route.name === 'vote') {
			endPoint = endPoint + '/poll/' + context.rootState.route.params.id
		} else if (context.rootState.route.name === 'list' && context.rootState.route.params.id) {
			endPoint = endPoint + '/poll/' + context.rootState.route.params.id
		} else {
			context.commit('reset')
			return
		}

		return axios.get(generateUrl(endPoint + '/comments'))
			.then((response) => {
				context.commit('set', response.data)
			})
			.catch(() => {
				context.commit('reset')
			})

	},

	add(context, payload) {
		let endPoint = 'apps/polls'

		if (context.rootState.route.name === 'publicVote') {
			endPoint = endPoint + '/s/' + context.rootState.route.params.token
		} else if (context.rootState.route.name === 'vote') {
			endPoint = endPoint + '/poll/' + context.rootState.route.params.id
		} else if (context.rootState.route.name === 'list' && context.rootState.route.params.id) {
			endPoint = endPoint + '/poll/' + context.rootState.route.params.id
		} else {
			context.commit('reset')
			return
		}

		return axios.post(generateUrl(endPoint + '/comment'), {
			message: payload.message,
		})
			.then((response) => {
				context.commit('add', { comment: response.data.comment })
				return response.data
			})
			.catch((error) => {
				console.error('Error writing comment', { error: error.response }, { payload: payload })
				throw error
			})
	},

	delete(context, payload) {
		let endPoint = 'apps/polls'

		if (context.rootState.route.name === 'publicVote') {
			endPoint = endPoint + '/s/' + context.rootState.route.params.token
		}

		return axios.delete(generateUrl(endPoint + '/comment/' + payload.comment.id))
			.then((response) => {
				context.commit('delete', { comment: payload.comment })
				return response.data
			})
			.catch((error) => {
				console.error('Error deleting comment', { error: error.response }, { payload: payload })
				throw error
			})
	},
}

export default { namespaced, state, mutations, actions, getters }
