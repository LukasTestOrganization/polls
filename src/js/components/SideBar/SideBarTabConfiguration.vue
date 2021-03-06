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
	<div>
		<div v-if="participantsVoted" class="warning">
			{{ t('polls', 'Please be careful when changing options, because it can affect existing votes in an unwanted manner.') }}
		</div>

		<ConfigBox v-if="!acl.isOwner" :title="t('polls', 'As an admin you may edit this poll')" icon-class="icon-checkmark" />

		<ConfigBox :title="t('polls', 'Title')" icon-class="icon-sound">
			<ConfigTitle @change="writePoll" />
		</ConfigBox>

		<ConfigBox :title="t('polls', 'Description')" icon-class="icon-edit">
			<ConfigDescription @change="writePoll" />
		</ConfigBox>

		<ConfigBox :title="t('polls', 'Poll configurations')" icon-class="icon-category-customization">
			<ConfigAllowComment @change="writePoll" />
			<ConfigAllowMayBe @change="writePoll" />
			<ConfigUseNo @change="writePoll" />
			<ConfigAnonymous @change="writePoll" />

			<ConfigVoteLimit @change="writePoll" />
			<ConfigOptionLimit @change="writePoll" />
		</ConfigBox>

		<ConfigBox :title="t('polls', 'Poll closing status')" :icon-class="closed ? 'icon-polls-closed' : 'icon-polls-open'">
			<ConfigClosing @change="writePoll" />
		</ConfigBox>

		<ConfigBox :title="t('polls', 'Access')" icon-class="icon-category-auth">
			<ConfigAdminAccess v-if="acl.isOwner" @change="writePoll" />
			<ConfigAccess @change="writePoll" />
		</ConfigBox>

		<ConfigBox :title="t('polls', 'Result display')" icon-class="icon-screen">
			<ConfigShowResults @change="writePoll" />
		</ConfigBox>

		<ButtonDiv :icon="poll.deleted ? 'icon-history' : 'icon-category-app-bundles'"
			:title="poll.deleted ? t('polls', 'Restore poll') : t('polls', 'Archive poll')"
			@click="toggleArchive()" />

		<ButtonDiv v-if="poll.deleted"
			icon="icon-delete"
			class="error"
			:title="t('polls', 'Delete poll')"
			@click="deletePoll()" />
	</div>
</template>

<script>
import debounce from 'lodash/debounce'
import { mapGetters, mapState } from 'vuex'
import { showSuccess, showError } from '@nextcloud/dialogs'
import moment from '@nextcloud/moment'
import ConfigBox from '../Base/ConfigBox'
import ConfigAccess from '../Configuration/ConfigAccess'
import ConfigAdminAccess from '../Configuration/ConfigAdminAccess'
import ConfigAllowComment from '../Configuration/ConfigAllowComment'
import ConfigAllowMayBe from '../Configuration/ConfigAllowMayBe'
import ConfigAnonymous from '../Configuration/ConfigAnonymous'
import ConfigClosing from '../Configuration/ConfigClosing'
import ConfigDescription from '../Configuration/ConfigDescription'
import ConfigOptionLimit from '../Configuration/ConfigOptionLimit'
import ConfigShowResults from '../Configuration/ConfigShowResults'
import ConfigTitle from '../Configuration/ConfigTitle'
import ConfigUseNo from '../Configuration/ConfigUseNo'
import ConfigVoteLimit from '../Configuration/ConfigVoteLimit'

export default {
	name: 'SideBarTabConfiguration',

	components: {
		ConfigBox,
		ConfigAccess,
		ConfigAdminAccess,
		ConfigAllowComment,
		ConfigAllowMayBe,
		ConfigAnonymous,
		ConfigClosing,
		ConfigDescription,
		ConfigOptionLimit,
		ConfigShowResults,
		ConfigTitle,
		ConfigUseNo,
		ConfigVoteLimit,
	},

	computed: {
		...mapState({
			poll: (state) => state.poll,
			acl: (state) => state.poll.acl,
		}),

		...mapGetters({
			closed: 'poll/isClosed',
			participantsVoted: 'poll/participantsVoted',
		}),
	},

	methods: {

		successDebounced: debounce(function(title) {
			showSuccess(t('polls', '"{pollTitle}" successfully saved', { pollTitle: this.poll.title }))
		}, 1500),

		toggleArchive() {
			if (this.poll.deleted) {
				this.$store.commit('poll/setProperty', { deleted: 0 })
			} else {
				this.$store.commit('poll/setProperty', { deleted: moment.utc().unix() })
			}
			this.writePoll()
		},

		async deletePoll() {
			if (!this.poll.deleted) return
			try {
				await this.$store.dispatch('poll/delete', { pollId: this.poll.id })
				this.$router.push({ name: 'list', params: { type: 'relevant' } })
			} catch {
				showError(t('polls', 'Error deleting poll.'))
			}
		},

		async writePoll() {
			if (this.poll.title) {
				try {
					await this.$store.dispatch('poll/update')
					this.successDebounced(this.poll.title)
				} catch {
					showError(t('polls', 'Error writing poll'))
				}
			} else {
				showError(t('polls', 'Title must not be empty!'))
			}
		},

	},
}
</script>
