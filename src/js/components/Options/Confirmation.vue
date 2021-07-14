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
	<div class="confirmation" :class=" { 'icon-polls-confirmed': isConfirmed }">
		<div class="confirmation--text">
			{{ confirmations }}
		</div>
	</div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
	name: 'Confirmation',

	props: {
		option: {
			type: Object,
			default: undefined,
		},
	},

	computed: {
		...mapState({
			poll: (state) => state.poll,
			votes: (state) => state.votes.list,
		}),

		...mapGetters({
			participantsVoted: 'poll/participantsVoted',
			closed: 'poll/isClosed',
			confirmedOptions: 'options/confirmed',
		}),

		isConfirmed() {
			return this.option.confirmed && this.closed
		},
		confirmations() {
			if (this.isConfirmed) {
				return t('polls', 'Confirmed')
			}
			return ' '

		},
	},
}

</script>

<style lang="scss">

.confirmation {
	align-items: center;
	justify-content: center;
	background-repeat: no-repeat;
	background-position: center;
	background-size: 21px;
	font-size: 0;
	align-self: stretch;
	min-width: 24px;
}

.confirmation--text {
	text-align: center;
}

</style>
