/*
 * @copyright Copyright (c) 2019 Rene Gieling <github@dartcafe.de>
 *
 * @author Rene Gieling <github@dartcafe.de>
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
import sortBy from 'lodash/sortBy'
import moment from 'moment'

const defaultOptions = () => {
	return {
		list: []
	}
}

const state = defaultOptions()

const mutations = {
	optionsSet(state, payload) {
		Object.assign(state, payload)
	},

	reset(state) {
		Object.assign(state, defaultOptions())
	},

	optionRemove(state, payload) {
		state.list = state.list.filter(option => {
			return option.id !== payload.option.id
		})
	},

	setOption(state, payload) {
		let index = state.list.findIndex((option) => {
			return option.id === payload.option.id
		})

		if (index < 0) {
			state.list.push(payload.option)
		} else {
			state.list.splice(index, 1, payload.option)
		}
	}
}

const getters = {
	lastOptionId: state => {
		return Math.max.apply(Math, state.list.map(function(option) { return option.id }))
	},

	sortedOptions: state => {
		return sortBy(state.list, 'timestamp')
	}
}

const actions = {

	loadPoll({ commit, rootState }, payload) {
		commit('reset')
		let endPoint = 'apps/polls/get/options/'

		if (payload.token !== undefined) {
			endPoint = endPoint.concat('s/', payload.token)
		} else if (payload.pollId !== undefined) {
			endPoint = endPoint.concat(payload.pollId)
		} else {
			return
		}

		return axios.get(OC.generateUrl(endPoint))
			.then((response) => {
				commit('optionsSet', { 'list': response.data })
			}, (error) => {
				console.error('Error loading options', { 'error': error.response }, { 'payload': payload })
				throw error
			})
	},

	updateOptionAsync({ commit, getters, dispatch, rootState }, payload) {
		return axios.post(OC.generateUrl('apps/polls/update/option'), { option: payload.option })
			.then((response) => {
				commit('setOption', { 'option': payload.option })
			}, (error) => {
				console.error('Error updating option', { 'error': error.response }, { 'payload': payload })
				throw error
			})
	},

	addOptionAsync({ commit, getters, dispatch, rootState }, payload) {
		let option = {}

		option.id = 0
		option.pollId = rootState.event.id

		if (rootState.event.type === 'datePoll') {
			option.timestamp = moment(payload.pollOptionText).unix()
			option.pollOptionText = moment.utc(payload.pollOptionText).format('YYYY-MM-DD HH:mm:ss')

		} else if (rootState.event.type === 'textPoll') {
			option.timestamp = 0
			option.pollOptionText = payload.pollOptionText
		}

		return axios.post(OC.generateUrl('apps/polls/add/option'), { option: option })
			.then((response) => {
				commit('setOption', { 'option': response.data })
			}, (error) => {
				console.error('Error adding option', { 'error': error.response }, { 'payload': payload })
				throw error
			})
	},

	removeOptionAsync({ commit, getters, dispatch, rootState }, payload) {
		return axios.post(OC.generateUrl('apps/polls/remove/option'), { option: payload.option })
			.then((response) => {
				commit('optionRemove', { 'option': payload.option })
			}, (error) => {
				console.error('Error removing option', { 'error': error.response }, { 'payload': payload })
				throw error
			})
	}
}

export default { state, mutations, getters, actions }
