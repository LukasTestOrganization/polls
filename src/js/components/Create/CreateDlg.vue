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
	<div class="create-dialog">
		<ConfigBox :title="t('polls', 'Title')" icon-class="icon-sound">
			<input id="pollTitle"
				ref="pollTitle"
				v-model="title"
				type="text"
				:placeholder="t('polls', 'Enter Title')"
				@keyup.enter="confirm">
		</ConfigBox>

		<ConfigBox :title="t('polls', 'Poll type')" icon-class="icon-checkmark">
			<RadioGroupDiv v-model="pollType" :options="pollTypeOptions" />
		</ConfigBox>

		<div class="create-buttons">
			<button class="button" @click="cancel">
				{{ t('polls', 'Cancel') }}
			</button>
			<button :disabled="titleEmpty" class="button primary" @click="confirm">
				{{ t('polls', 'Apply') }}
			</button>
		</div>
	</div>
</template>

<script>
import { mapState } from 'vuex'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { emit } from '@nextcloud/event-bus'
import ConfigBox from '../Base/ConfigBox'
import RadioGroupDiv from '../Base/RadioGroupDiv'

export default {
	name: 'CreateDlg',

	components: {
		ConfigBox,
		RadioGroupDiv,
	},

	data() {
		return {
			id: 0,
			pollType: 'datePoll',
			title: '',
			pollTypeOptions: [
				{ value: 'datePoll', label: t('polls', 'Date poll') },
				{ value: 'textPoll', label: t('polls', 'Text poll') },
			],
		}
	},

	computed: {
		...mapState({
			poll: (state) => state.poll,
		}),

		titleEmpty() {
			return this.title === ''
		},
	},

	methods: {
		cancel() {
			this.title = ''
			this.pollType = 'datePoll'
			this.$emit('close-create')
		},

		async confirm() {
			try {
				const response = await this.$store.dispatch('poll/add', { title: this.title, type: this.pollType })
				emit('update-polls')
				this.cancel()
				showSuccess(t('polls', 'Poll "{pollTitle}" added', { pollTitle: response.data.title }))
				this.$router.push({ name: 'vote', params: { id: response.data.id } })
			} catch {
				showError(t('polls', 'Error while creating Poll "{pollTitle}"', { pollTitle: this.title }))
			}
		},

		setFocus() {
			this.$nextTick(() => {
				this.$refs.pollTitle.focus()
			})
		},
	},
}
</script>

<style lang="css">
.create-dialog {
	background-color: var(--color-main-background);
	padding: 8px 20px;
}

.create-buttons {
	display: flex;
	justify-content: space-between;
}
</style>
