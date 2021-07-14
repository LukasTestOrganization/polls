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
	<div class="poll-title">
		<div class="poll-title__title">
			{{ title }}
		</div>
		<div v-if="showSubText" class="poll-title__sub">
			<span>{{ subTextLeft }}</span> |
			<span :class="[subTextRight.class, subTextRight.icon ]">{{ subTextRight.text }}</span>
		</div>
	</div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import moment from '@nextcloud/moment'

export default {
	name: 'PollTitle',

	props: {
		showSubText: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
		...mapState({
			title: (state) => state.poll.title,
			expire: (state) => state.poll.expire,
			deleted: (state) => state.poll.deleted,
			ownerDisplayName: (state) => state.poll.ownerDisplayName,
			pollCreated: (state) => state.poll.created,
		}),

		...mapGetters({
			isClosed: 'poll/isClosed',
			proposalsExpirySet: 'poll/proposalsExpirySet',
			proposalsExpired: 'poll/proposalsExpired',
			proposalsExpireRelative: 'poll/proposalsExpireRelative',
		}),

		subTextLeft() {
			return t('polls', 'A poll from {name}', { name: this.ownerDisplayName })
		},

		subTextRight() {
			if (this.deleted) {
				return {
					text: t('polls', 'Archived'),
					icon: 'icon-category-app-bundles',
					class: 'archived',
				}
			}

			if (this.isClosed) {
				return {
					text: t('polls', '{relativeTimeAgo}', { relativeTimeAgo: this.timeExpirationRelative }),
					icon: 'icon-polls-closed',
					class: 'closed',
				}
			}

			if (this.proposalsExpirySet && !this.proposalsExpired) {
				return {
					text: t('polls', 'Proposals period ends {timeRelative}.', { timeRelative: this.proposalsExpireRelative }),
					icon: 'icon-add',
					class: 'proposal',
				}
			}

			if (!this.isClosed && this.expire) {
				return {
					text: t('polls', 'Closing {relativeExpirationTime}', { relativeExpirationTime: this.timeExpirationRelative }),
					icon: 'icon-calendar-000',
					class: this.closeToClosing ? 'closing' : 'open',
				}
			}

			return {
				text: t('polls', '{dateRelative}', { dateRelative: this.dateCreatedRelative }),
				icon: 'icon-clock',
				class: 'created',
			}
		},

		dateCreatedRelative() {
			return moment.unix(this.pollCreated).fromNow()
		},

		closeToClosing() {
			return (!this.isClosed && this.expire && moment.unix(this.expire).diff() < 86400000)
		},

		timeExpirationRelative() {
			if (this.expire) {
				return moment.unix(this.expire).fromNow()
			}
			return t('polls', 'never')

		},
	},
}

</script>

<style lang="scss">
	.poll-title {
		.poll-title__sub {
			opacity: 0.7;
			line-height: 1.2em;
			font-size: 1em;
			[class^="icon-"], [class*=" icon-"] {
				padding-left: 21px;
				background-position: left center;
			}
			.closed {
				color: var(--color-error);
				font-weight: 700;
			}
			.closing {
				color: var(--color-warning);
				font-weight: 700;
			}
			.open {
				// color: var(--color-success);
				font-weight: 700;
			}
			.archived {
				color: var(--color-error);
				font-weight: 700;
			}
			.created {
				color: var(--color-text-light);
			}
		}

		.poll-title__title {
			font-weight: bold;
			font-size: 1.3em;
			line-height: 2em;
			color: var(--color-text-light);
		}
	}
</style>
