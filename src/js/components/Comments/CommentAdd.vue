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
	<div class="comment-add">
		<UserItem v-bind="acl" hide-names />
		<InputDiv v-model="comment"
			class="comment-add__input"
			:placeholder="t('polls', 'New comment …')"
			@submit="writeComment()" />
	</div>
</template>

<script>
import { mapState } from 'vuex'
import { showError } from '@nextcloud/dialogs'
import InputDiv from '../Base/InputDiv'

export default {
	name: 'CommentAdd',

	components: {
		InputDiv,
	},

	data() {
		return {
			comment: '',
		}
	},

	computed: {
		...mapState({
			acl: (state) => state.poll.acl,
		}),

	},

	methods: {
		async writeComment() {
			if (this.comment) {
				try {
					await this.$store.dispatch('comments/add', { message: this.comment })
					this.comment = ''
				} catch {
					showError(t('polls', 'Error while saving comment'))
				}
			}
		},
	},
}
</script>

<style lang="scss">
	.comment-add {
		margin-bottom: 24px;
		display: flex;
		.comment-add__input {
			margin-left: 8px;
			flex: 1;
			align-items: center;
		}
	}
</style>
