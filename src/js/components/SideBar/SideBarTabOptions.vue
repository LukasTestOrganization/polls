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
	<div class="side-bar-tab-options">
		<ConfigBox v-if="!isOwner" :title="t('polls', 'As an admin you may edit this poll')" icon-class="icon-checkmark" />
		<ConfigBox :title="t('polls', 'Allow proposals from users')" icon-class="icon-category-customization">
			<ConfigProposals />
		</ConfigBox>

		<ConfigBox v-if="pollType === 'datePoll' && countOptions && !closed" :title="t('polls', 'Shift all date options')" icon-class="icon-polls-move">
			<OptionsDateShift />
		</ConfigBox>

		<ConfigBox :title="t('polls', 'Available Options')" :icon-class="pollTypeIcon">
			<OptionsDate v-if="pollType === 'datePoll'" />
			<OptionsText v-else-if="pollType === 'textPoll'" />
		</ConfigBox>
	</div>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import ConfigBox from '../Base/ConfigBox'
import OptionsDate from '../Options/OptionsDate'
import OptionsDateShift from '../Options/OptionsDateShift'
import OptionsText from '../Options/OptionsText'
import ConfigProposals from '../Configuration/ConfigProposals'

export default {
	name: 'SideBarTabOptions',

	components: {
		ConfigBox,
		ConfigProposals,
		OptionsDate,
		OptionsDateShift,
		OptionsText,
	},

	computed: {
		...mapGetters({
			closed: 'poll/isClosed',
			countOptions: 'options/count',
			pollTypeIcon: 'poll/typeIcon',
		}),
		...mapState({
			pollType: (state) => state.poll.type,
			isOwner: (state) => state.poll.acl.isOwner,
		}),
	},
}
</script>

<style lang="scss">
.side-bar-tab-options {
	.owner {
		display: flex;
		position: relative;
		left: -16px;
		width: 0;
	}

	.draggable:hover .owner {
		display: none;
	}

	.option-item {
		border-bottom: 1px solid var(--color-border);
		&:active,
		&:hover {
			transition: var(--background-dark) 0.3s ease;
			background-color: var(--color-background-dark);
		}
	}
}

</style>
