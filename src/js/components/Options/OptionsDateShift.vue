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
		<div v-if="proposalsExist">
			{{ t('polls', 'Shifting dates is disabled to prevent shifting of other user\'s proposals.') }}
		</div>
		<div v-else class="selectUnit">
			<InputDiv v-model="shift.step"
				use-num-modifiers
				@add="shift.step += 1"
				@subtract="shift.step -= 1" />
			<Multiselect v-model="shift.unit"
				:options="dateUnits"
				label="name"
				track-by="value" />
			<ButtonDiv submit
				@click="shiftDates(shift)" />
		</div>
	</div>
</template>

<script>

import { mapState, mapGetters } from 'vuex'
import InputDiv from '../Base/InputDiv'
import moment from '@nextcloud/moment'
import { Multiselect } from '@nextcloud/vue'
import { dateUnits } from '../../mixins/dateMixins'

export default {
	name: 'OptionsDateShift',

	components: {
		InputDiv,
		Multiselect,
	},

	mixins: [dateUnits],

	data() {
		return {
			sequence: {
				baseOption: {},
				unit: { name: t('polls', 'Week'), value: 'week' },
				step: 1,
				amount: 1,
			},
			shift: {
				step: 1,
				unit: { name: t('polls', 'Week'), value: 'week' },
			},
		}
	},

	computed: {
		...mapState({
			options: (state) => state.options.list,
		}),

		...mapGetters({
			proposalsExist: 'options/proposalsExist',
		}),

		firstDOW() {
			// vue2-datepicker needs 7 for sunday
			if (moment.localeData()._week.dow === 0) {
				return 7
			}
			return moment.localeData()._week.dow

		},
	},

	methods: {
		shiftDates(payload) {
			this.$store.dispatch('options/shift', { shift: payload })
		},
	},
}

</script>

<style lang="scss">
.selectUnit {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	.multiselect {
		margin: 0 8px;
		width: unset !important;
		min-width: 75px;
		flex: 1;
	}
}

</style>
