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
	<InputDiv v-model="newPollText"
		:placeholder="placeholder"
		@submit="addOption()" />
</template>

<script>
import { showError, showSuccess } from '@nextcloud/dialogs'
import InputDiv from '../Base/InputDiv'

export default {
	name: 'OptionsTextAdd',

	components: {
		InputDiv,
	},

	props: {
		placeholder: {
			type: String,
			default: t('polls', 'Add option'),
		},
	},

	data() {
		return {
			newPollText: '',
		}
	},

	methods: {
		async addOption() {
			if (this.newPollText) {
				try {
					await this.$store.dispatch('options/add', { pollOptionText: this.newPollText })
					showSuccess(t('polls', '{optionText} added', { optionText: this.newPollText }))
					this.newPollText = ''
				} catch (e) {
					if (e.response.status === 409) {
						showError(t('polls', '{optionText} already exists', { optionText: this.newPollText }))
					} else {
						showError(t('polls', 'Error adding {optionText}', { optionText: this.newPollText }))
					}
				}
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
