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
		<OptionsTextAdd v-if="!closed" />
		<draggable v-if="countOptions" v-model="reOrderedOptions">
			<transition-group>
				<OptionItem v-for="(option) in reOrderedOptions"
					:key="option.id"
					:option="option"
					:draggable="true">
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
							<ActionButton v-if="closed"
								:icon="option.confirmed ? 'icon-polls-yes' : 'icon-checkmark'"
								@click="confirmOption(option)">
								{{ option.confirmed ? t('polls', 'Unconfirm option') : t('polls', 'Confirm option') }}
							</ActionButton>
						</Actions>
					</template>
				</OptionItem>
			</transition-group>
		</draggable>

		<EmptyContent v-else :icon="pollTypeIcon">
			{{ t('polls', 'No vote options') }}
			<template #desc>
				{{ t('polls', 'Add some!') }}
			</template>
		</EmptyContent>
	</div>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import { Actions, ActionButton, EmptyContent } from '@nextcloud/vue'
import draggable from 'vuedraggable'
import ActionDelete from '../Actions/ActionDelete'
import OptionItem from './OptionItem'
import OptionItemOwner from '../Options/OptionItemOwner'
import { confirmOption, removeOption } from '../../mixins/optionMixins'

export default {
	name: 'OptionsText',

	components: {
		Actions,
		ActionButton,
		ActionDelete,
		EmptyContent,
		draggable,
		OptionItem,
		OptionItemOwner,
		OptionsTextAdd: () => import('./OptionsTextAdd'),
	},

	mixins: [
		confirmOption,
		removeOption,
	],

	data() {
		return {
			newPollText: '',
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

		reOrderedOptions: {
			get() {
				return this.options
			},
			set(value) {
				this.$store.dispatch('options/reorder', value)
			},
		},

	},

	methods: {
		async addOption() {
			if (this.newPollText) {
				await this.$store.dispatch('options/add', { pollOptionText: this.newPollText })
				this.newPollText = ''
			}
		},
	},
}
</script>

<style lang="scss">
	.optionAdd {
		display: flex;
	}

	.newOption {
		margin-left: 40px;
		flex: 1;
		&:empty:before {
			color: grey;
		}
	}

	.submit-option {
		width: 30px;
		background-color: transparent;
		border: none;
		opacity: 0.3;
		cursor: pointer;
	}

</style>
