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
	<div class="vote-table" :class="[viewMode, { closed: closed }]">
		<div class="vote-table__users">
			<div class="spacer" />

			<div v-for="(participant) in participants"
				:key="participant.userId"
				:class="['participant', {currentuser: (participant.userId === acl.userId) }]">
				<UserItem v-bind="participant" condensed />

				<ActionDelete v-if="acl.allowEdit"
					:title="t('polls', 'Delete votes')"
					@delete="removeUser(participant.userId)" />
			</div>

			<div v-if="proposalsExist" class="owner" />

			<div v-if="acl.allowEdit && closed" class="confirm" />
		</div>

		<transition-group name="list" tag="div" class="vote-table__votes">
			<VoteColumn v-for="(item) in options"
				:key="item.id"
				:option="item"
				:view-mode="viewMode" />
		</transition-group>
	</div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import { showSuccess } from '@nextcloud/dialogs'
import ActionDelete from '../Actions/ActionDelete'
import VoteColumn from './VoteColumn'
import { confirmOption } from '../../mixins/optionMixins'

export default {
	name: 'VoteTable',
	components: {
		ActionDelete,
		VoteColumn,
	},

	mixins: [confirmOption],

	props: {
		viewMode: {
			type: String,
			default: 'table-view',
		},
	},

	data() {
		return {
			modal: false,
			userToRemove: '',
		}
	},

	computed: {
		...mapState({
			acl: (state) => state.poll.acl,
		}),

		...mapGetters({
			closed: 'poll/isClosed',
			participants: 'poll/safeParticipants',
			options: 'options/rankedOptions',
			proposalsExist: 'options/proposalsExist',
		}),

	},

	methods: {
		async removeUser(userId) {
			await this.$store.dispatch('votes/deleteUser', { userId })
			showSuccess(t('polls', 'User {userId} removed', { userId }))
		},

	},
}
</script>

<style lang="scss">
.vote-table {
	display: flex;
	flex: 1;

	.participant {
		display: flex;
	}
	.participant, .vote-item {
		flex: 0 0 auto;
		height: 4.5em;
		order: 10;
		line-height: 1.5em;
		padding: 4px;
		border-top: solid 1px var(--color-border-dark);
		&.currentuser {
			order:5;
		}
	}

	.vote-table__users {
		display: flex;
		flex-direction: column;
		overflow-x: scroll;
	}

	.vote-table__votes {
		display: flex;
		flex: 1;
		overflow-x: scroll;
	}

	.vote-column {
		order: 2;
		display: flex;
		flex: 1 0 auto;
		flex-direction: column;
		align-items: stretch;
		min-width: 85px;
		max-width: 280px;
		border-left: 1px solid var(--color-border-dark);
		&>div {
			display: flex;
			justify-content: center;
			align-items: center;
		}
		.vote-table-header-item {
			align-items: stretch;
			flex: 1;
			// padding: 0 8px;
			order: 1;
		}
	}

	&.closed .vote-column {
		&.confirmed {
			order: 1;
			border-radius: 10px;
			border: 1px solid var(--color-polls-foreground-yes);
			background-color: var(--color-polls-background-yes);
			margin: 0 4px;
		}
	}

	.vote-item {
		background-clip: content-box;
	}

	.confirmation {
		order:3;
		padding: 4px;
	}

	.counter {
		order:3;
	}

	.calendar-peek {
		order:2;
	}

	.confirm {
		height: 45px;
		order: 20;
	}

	.owner {
		display: flex;
		flex: 0 auto;
		height: 3.5em;
		line-height: 3.5em;
		min-width: 56px;
		order: 19;
	}

	.spacer {
		flex: 1;
		order: 1;
	}

	&.table-view {
		.vote-table__users::after, .vote-column::after {
			content: '';
			height: 8px;
			order: 99;
		}

		.participant {
			max-width: 245px;
		}

		.option-item .option-item__option--text {
			text-align: center;
		}
	}

	&.list-view {
		flex-direction: column;

		&.closed {
			.counter {
				padding-left: 60px;
			}

			.vote-item:not(.confirmed) {
				background-color: var(--color-main-background);
				&.no > .icon {
					background-image: var(--icon-polls-no)
				}
			}

			.vote-column {
				padding: 2px 8px;
				&.confirmed {
					margin: 4px 0;
				}
			}
		}

		.vote-table__users .confirm {
			display: none;
		}

		.vote-column {
			flex-direction: row-reverse;
			align-items: center;
			max-width: initial;
			position: relative;
			border-top: solid 1px var(--color-border);
			padding: 0;
		}

		.vote-table__users {
			margin: 0
		}

		.participant:not(.currentuser), .vote-item:not(.currentuser) {
			display: none;
		}

		.vote-table__votes {
			align-items: stretch;
			flex-direction: column;
		}

		.vote-table-header-item {
			flex-direction: row;
			.option-item {
				padding: 8px 4px;
			}
		}

		.counter {
			&.barStyle {
				position: absolute;
				bottom: 0;
				width: 100%;
				padding-left: 44px;
			}
			&.iconStyle {
				order: 0;
				padding-left: 44px;
			}
		}

		.vote-item.currentuser {
			border: none;
		}

		.owner {
			order: 0;
		}

		.calendar-peek {
			order: 0;
		}
		.calendar-peek__conflict.icon {
			width: 24px;
			height: 24px;
		}
	}
}

</style>
