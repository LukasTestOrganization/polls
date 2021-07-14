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
		<OptionsDateAdd v-if="!closed" />
		<transition-group is="ul" v-if="countOptions">
			<OptionItem v-for="(option) in options"
				:key="option.id"
				:option="option"
				:show-confirmed="true"
				display="textBox"
				tag="li">
				<template #icon>
					<OptionItemOwner v-if="acl.allowAddOptions"
						:avatar-size="16"
						:option="option"
						class="owner" />
				</template>
				<template #actions>
					<ActionDelete v-if="acl.allowEdit"
						:title="t('polls', 'Delete option')"
						@delete="removeOption(option)" />
					<Actions v-if="acl.allowEdit" class="action">
						<ActionButton v-if="!closed" icon="icon-polls-clone" @click="cloneOptionModal(option)">
							{{ t('polls', 'Clone option') }}
						</ActionButton>
						<ActionButton v-if="closed"
							:icon="option.confirmed ? 'icon-polls-confirmed' : 'icon-polls-unconfirmed'"
							@click="confirmOption(option)">
							{{ option.confirmed ? t('polls', 'Unconfirm option') : t('polls', 'Confirm option') }}
						</ActionButton>
					</Actions>
				</template>
			</OptionItem>
		</transition-group>

		<EmptyContent v-else :icon="pollTypeIcon">
			{{ t('polls', 'No vote options') }}
			<template #desc>
				{{ t('polls', 'Add some!') }}
			</template>
		</EmptyContent>

		<Modal v-if="cloneModal" :can-close="false">
			<OptionCloneDate :option="optionToClone" class="modal__content" @close="closeModal()" />
		</Modal>
	</div>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import moment from '@nextcloud/moment'
import { Actions, ActionButton, EmptyContent, Modal } from '@nextcloud/vue'
import ActionDelete from '../Actions/ActionDelete'
import OptionCloneDate from './OptionCloneDate'
import OptionItem from './OptionItem'
import OptionItemOwner from '../Options/OptionItemOwner'
import { confirmOption, removeOption } from '../../mixins/optionMixins'
import { dateUnits } from '../../mixins/dateMixins'

export default {
	name: 'OptionsDate',

	components: {
		Actions,
		ActionButton,
		ActionDelete,
		EmptyContent,
		Modal,
		OptionCloneDate,
		OptionsDateAdd: () => import('./OptionsDateAdd'),
		OptionItem,
		OptionItemOwner,
	},

	mixins: [
		confirmOption,
		dateUnits,
		removeOption,
	],

	data() {
		return {
			cloneModal: false,
			optionToClone: {},
			sequence: {
				baseOption: {},
				unit: { name: t('polls', 'Week'), value: 'week' },
				step: 1,
				amount: 1,
			},
		}
	},

	computed: {
		...mapState({
			options: (state) => state.options.list,
			acl: (state) => state.poll.acl,
			isOwner: (state) => state.poll.acl.isOwner,
		}),

		...mapGetters({
			closed: 'poll/isClosed',
			countOptions: 'options/count',
			pollTypeIcon: 'poll/typeIcon',
		}),

		dateBaseOptionString() {
			return moment.unix(this.sequence.baseOption.timestamp).format('LLLL')
		},
	},

	methods: {
		closeModal() {
			this.cloneModal = false
		},

		cloneOptionModal(option) {
			this.optionToClone = option
			this.cloneModal = true
		},

	},
}
</script>
