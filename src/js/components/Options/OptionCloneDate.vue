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
	<div class="option-clone-date">
		<h2>{{ t('polls', 'Clone to option sequence') }}</h2>

		<p>{{ t('polls', 'Create a sequence of date options starting with {dateOption}.', { dateOption: dateBaseOptionString }) }}</p>
		<div>
			<h3> {{ t('polls', 'Step unit:') }} </h3>
			<Multiselect v-model="sequence.unit"
				:options="dateUnits"
				label="name"
				track-by="value" />
			<h3> {{ t('polls', 'Step width:') }} </h3>
			<input v-model="sequence.step">
			<h3>{{ t('polls', 'Number of items to create:') }}</h3>
			<input v-model="sequence.amount">
		</div>

		<div class="buttons">
			<ButtonDiv :title="t('polls', 'Cancel')" @click="$emit('close')" />
			<ButtonDiv :primary="true" :title="t('polls', 'OK')" @click="createSequence" />
		</div>
	</div>
</template>

<script>

import moment from '@nextcloud/moment'
import { Multiselect } from '@nextcloud/vue'
import { dateUnits } from '../../mixins/dateMixins'

export default {
	name: 'OptionCloneDate',

	components: {
		Multiselect,
	},

	mixins: [dateUnits],

	props: {
		option: {
			type: Object,
			default: undefined,
		},
	},

	data() {
		return {
			sequence: {
				unit: { name: t('polls', 'Week'), value: 'week' },
				step: 1,
				amount: 1,
			},
		}
	},

	computed: {
		dateBaseOptionString() {
			return moment.unix(this.option.timestamp).format('LLLL')
		},
	},

	methods: {
		createSequence() {
			this.$store
				.dispatch('options/sequence', {
					option: this.option,
					sequence: this.sequence,
				})
			this.$emit('close')
		},
	},
}

</script>

<style lang="scss">
.buttons {
	display: flex;
	justify-content: flex-end;
	align-items: center;
	.button {
		margin-left: 10px;
		margin-right: 0;
	}
}

</style>
