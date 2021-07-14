<!--
  - @copyright Copyright (c) 2018 René Gieling <github@dartcafe.de>
  -
  - @author René Gieling <github@dartcafe.de>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
	<AppContent class="poll-list">
		<div class="area__header">
			<div>
				<h2 class="title">
					{{ title }}
				</h2>
				<h3 class="description">
					{{ description }}
				</h3>
			</div>
		</div>

		<div class="area__main">
			<EmptyContent v-if="noPolls" icon="icon-polls">
				{{ t('polls', 'No polls found for this category') }}
				<template #desc>
					{{ t('polls', 'Add one or change category!') }}
				</template>
			</EmptyContent>

			<transition-group v-else
				name="list"
				tag="div"
				class="poll-list__list">
				<PollItem key="0"
					:header="true"
					:sort="sort"
					:reverse="reverse"
					@sort-list="setSort($event)" />

				<PollItem v-for="(poll) in sortedList"
					:key="poll.id"
					:poll="poll"
					@goto-poll="gotoPoll(poll.id)"
					@load-poll="loadPoll(poll.id)">
					<template #actions>
						<Actions :force-menu="true">
							<ActionButton icon="icon-add"
								:close-after-click="true"
								@click="clonePoll(poll.id)">
								{{ t('polls', 'Clone poll') }}
							</ActionButton>

							<ActionButton v-if="poll.allowEdit && !poll.deleted"
								icon="icon-category-app-bundles"
								:close-after-click="true"
								@click="toggleArchive(poll.id)">
								{{ t('polls', 'Archive poll') }}
							</ActionButton>

							<ActionButton v-if="poll.allowEdit && poll.deleted"
								icon="icon-history"
								:close-after-click="true"
								@click="toggleArchive(poll.id)">
								{{ t('polls', 'Restore poll') }}
							</ActionButton>

							<ActionButton v-if="poll.allowEdit && poll.deleted"
								icon="icon-delete"
								class="danger"
								:close-after-click="true"
								@click="deletePoll(poll.id)">
								{{ t('polls', 'Delete poll') }}
							</ActionButton>
						</Actions>
					</template>
				</PollItem>
			</transition-group>
		</div>
		<LoadingOverlay v-if="isLoading" />
	</AppContent>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import sortBy from 'lodash/sortBy'
import { showError } from '@nextcloud/dialogs'
import { emit } from '@nextcloud/event-bus'
import { Actions, ActionButton, AppContent, EmptyContent } from '@nextcloud/vue'

export default {
	name: 'PollList',

	components: {
		AppContent,
		Actions,
		ActionButton,
		LoadingOverlay: () => import('../components/Base/LoadingOverlay'),
		PollItem: () => import('../components/PollList/PollItem'),
		EmptyContent,
	},

	data() {
		return {
			isLoading: false,
			sort: 'created',
			reverse: true,
		}
	},

	computed: {
		...mapState({
			pollCategories: (state) => state.polls.categories,
		}),

		...mapGetters({
			filteredPolls: 'polls/filtered',
		}),

		title() {
			return this.pollCategories.find((category) => (category.id === this.$route.params.type)).titleExt
		},

		description() {
			return this.pollCategories.find((category) => (category.id === this.$route.params.type)).description
		},

		windowTitle() {
			return t('polls', 'Polls') + ' - ' + this.title
		},

		sortedList() {
			if (this.reverse) {
				return sortBy(this.filteredPolls(this.$route.params.type), this.sort).reverse()
			}
			return sortBy(this.filteredPolls(this.$route.params.type), this.sort)
		},

		noPolls() {
			return this.sortedList.length < 1
		},

	},

	watch: {
		$route() {
			this.refreshView()
		},
	},

	mounted() {
		this.refreshView()
	},

	methods: {
		gotoPoll(pollId) {
			this.$router
				.push({ name: 'vote', params: { id: pollId } })
		},

		async loadPoll(pollId) {
			try {
				await this.$store.dispatch({ type: 'poll/get', pollId })
				emit('toggle-sidebar', { open: true })
			} catch {
				showError(t('polls', 'Error loading poll'))
			}
		},

		refreshView() {
			window.document.title = t('polls', 'Polls') + ' - ' + this.title
			if (!this.filteredPolls(this.$route.params.type).find((poll) => poll.id === this.$store.state.poll.id)) {
				emit('toggle-sidebar', { open: false })
			}

		},

		setSort(payload) {
			if (this.sort === payload.sort) {
				this.reverse = !this.reverse
			} else {
				this.sort = payload.sort
				this.reverse = true
			}
		},

		callPoll(index, poll, name) {
			this.$router.push({
				name,
				params: {
					id: poll.id,
				},
			})
		},

		async toggleArchive(pollId) {
			try {
				await this.$store.dispatch('poll/toggleArchive', { pollId })
			} catch {
				showError(t('polls', 'Error archiving/restoring poll.'))
			} finally {
				emit('update-polls')
			}
		},

		async deletePoll(pollId) {
			try {
				await this.$store.dispatch('poll/delete', { pollId })
			} catch {
				showError(t('polls', 'Error deleting poll.'))
			} finally {
				emit('update-polls')
			}
		},

		async clonePoll(pollId) {
			try {
				await this.$store.dispatch('poll/clone', { pollId })
			} catch {
				showError(t('polls', 'Error cloning poll.'))
			} finally {
				emit('update-polls')
			}
		},
	},
}
</script>

<style lang="scss">
	.poll-list__list {
		width: 100%;
		display: flex;
		flex-direction: column;
		overflow: scroll;
		padding-bottom: 14px;
	}
</style>
