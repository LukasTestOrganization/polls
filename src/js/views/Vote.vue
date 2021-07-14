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
	<AppContent :class="[{ closed: closed }, poll.type]">
		<PollHeaderButtons />
		<div class="area__header">
			<PollTitle showSubText />
			<MarkUpDescription />
		</div>

		<div v-if="acl.allowAddOptions && proposalsAllowed && !closed" class="area__proposal">
			<OptionProposals />
		</div>

		<div class="area__main" :class="viewMode">
			<VoteTable v-show="options.length" :view-mode="viewMode" />

			<EmptyContent v-if="!options.length" :icon="pollTypeIcon">
				{{ t('polls', 'No vote options available') }}
				<template #desc>
					<button v-if="acl.allowEdit" @click="openOptions">
						{{ t('polls', 'Add some!') }}
					</button>
					<div v-if="!acl.allowEdit">
						{{ t('polls', 'Maybe the owner did not provide some until now.') }}
					</div>
				</template>
			</EmptyContent>
		</div>

		<div v-if="countHiddenParticipants" class="area__footer">
			<h2>
				{{ t('polls', 'Due to performance concerns {countHiddenParticipants} voters are hidden.', { countHiddenParticipants }) }}
			</h2>
		</div>

		<div v-if="poll.anonymous" class="area__footer">
			<div>
				{{ t('poll', 'Although participant\'s names are hidden, this is not a real anonymous poll because they are not hidden from the owner.') }}
				{{ t('poll', 'Additionally the owner can remove the anonymous flag at any time, which will reveal the participant\'s names.') }}
			</div>
		</div>

		<PublicRegisterModal v-if="showRegisterModal" />
		<LoadingOverlay v-if="isLoading" />
	</AppContent>
</template>

<script>
import { showError, showSuccess } from '@nextcloud/dialogs'
import { mapState, mapGetters } from 'vuex'
import { AppContent, EmptyContent } from '@nextcloud/vue'
import { getCurrentUser } from '@nextcloud/auth'
import { emit } from '@nextcloud/event-bus'
import MarkUpDescription from '../components/Poll/MarkUpDescription'
import PollTitle from '../components/Poll/PollTitle'
import PollHeaderButtons from '../components/Poll/PollHeaderButtons'

export default {
	name: 'Vote',
	components: {
		AppContent,
		MarkUpDescription,
		EmptyContent,
		LoadingOverlay: () => import('../components/Base/LoadingOverlay'),
		PollHeaderButtons,
		PollTitle,
		PublicRegisterModal: () => import('../components/Poll/PublicRegisterModal'),
		VoteTable: () => import('../components/VoteTable/VoteTable'),
		OptionProposals: () => import('../components/Options/OptionProposals'),
	},

	data() {
		return {
			delay: 50,
			isLoading: false,
			voteSaved: false,
		}
	},

	computed: {
		...mapState({
			poll: (state) => state.poll,
			acl: (state) => state.poll.acl,
			share: (state) => state.share,
			settings: (state) => state.settings,
		}),

		...mapGetters({
			closed: 'poll/isClosed',
			options: 'options/rankedOptions',
			pollTypeIcon: 'poll/typeIcon',
			viewMode: 'settings/viewMode',
			proposalsAllowed: 'poll/proposalsAllowed',
			countHiddenParticipants: 'poll/countHiddenParticipants',
			safeTable: 'poll/safeTable',
		}),

		showEmailEdit() {
			return ['email', 'contact', 'external'].includes(this.share.type)
		},

		windowTitle() {
			return t('polls', 'Polls') + ' - ' + this.poll.title
		},

		showRegisterModal() {
			return (this.$route.name === 'publicVote'
				&& ['public', 'email', 'contact'].includes(this.share.type)
				&& !this.closed
				&& this.poll.id
			)
		},

	},

	created() {
		// simulate @media:prefers-color-scheme until it is supported for logged in users
		// This simulates the theme--dark
		// TODO: remove, when completely supported by core
		if (!window.matchMedia) {
			return true
		} else if (this.$route.name === 'publicVote' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
			document.body.classList.add('theme--dark')
		}

		if (getCurrentUser() && this.$route.name === 'publicVote') {
			// reroute to the internal vote page, if the user is logged in
			this.rerouteToInternal()
		} else {
			emit('toggle-sidebar', { open: (window.innerWidth > 920) })
		}
	},

	beforeDestroy() {
		this.$store.dispatch({ type: 'poll/reset' })
	},

	methods: {
		openOptions() {
			emit('toggle-sidebar', { open: true, activeTab: 'options' })
		},

		async rerouteToInternal() {
			try {
				const response = await this.$store.dispatch('share/get', { token: this.$route.params.token })
				this.$router.replace({ name: 'vote', params: { id: response.share.pollId } })
			} catch (e) {
				this.$router.replace({ name: 'notfound' })
			}
		},

		async submitEmailAddress(emailAddress) {
			try {
				await this.$store.dispatch('share/updateEmailAddress', { emailAddress })
				showSuccess(t('polls', 'Email address {emailAddress} saved.', { emailAddress }))
			} catch {
				showError(t('polls', 'Error saving email address {emailAddress}', { emailAddress }))
			}
		},
	},
}

</script>

<style lang="scss">
.poll-title {
	margin-bottom: 16px;
}

.markup-description {
	font-size: 1.1em;
}

.icon.icon-settings.active {
	display: block;
	width: 44px;
	height: 44px;
}

</style>
