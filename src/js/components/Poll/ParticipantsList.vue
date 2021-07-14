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

<template lang="html">
	<div class="participants-list">
		<h2 v-if="countParticipantsVoted">
			{{ n('polls', '%n Participant', '%n Participants', countParticipantsVoted) }}
		</h2>
		<h2 v-else>
			{{ t('polls','No Participants until now') }}
		</h2>
		<div v-if="countParticipantsVoted" class="participants-list__list">
			<UserItem v-for="(participant) in participantsVoted"
				:key="participant.userId"
				v-bind="participant"
				:hide-names="true"
				type="user" />
			<Actions>
				<ActionButton v-if="poll.acl.allowEdit" icon="icon-clippy" @click="getAddresses()">
					{{ t('polls', 'Copy list of email addresses to clipboard') }}
				</ActionButton>
			</Actions>
		</div>
	</div>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { Actions, ActionButton } from '@nextcloud/vue'

export default {
	name: 'ParticipantsList',
	components: {
		Actions,
		ActionButton,
	},

	computed: {
		...mapState({
			poll: (state) => state.poll,
		}),

		...mapGetters({
			countParticipantsVoted: 'poll/countParticipantsVoted',
			participantsVoted: 'poll/participantsVoted',
		}),
	},

	methods: {
		async getAddresses() {
			try {
				const response = await this.$store.dispatch('poll/getParticipantsEmailAddresses', { pollId: this.poll.id })
				await this.$copyText(response.data)
				showSuccess(t('polls', 'Link copied to clipboard'))
			} catch {
				showError(t('polls', 'Error while copying link to clipboard'))
			}
		},
	},
}
</script>

<style lang="scss">
	.participants-list {
		padding: 8px;
	}

	.participants-list__list {
		display: flex;
		justify-content: flex-start;
		flex-wrap: wrap;
		&> * {
			flex: 0;
		}
	}
</style>
