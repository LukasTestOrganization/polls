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
	<div class="share-item" :class="type">
		<Avatar class="share-item__avatar" icon-class="icon-public" :is-no-user="true" />
		<div class="share-item__name">
			{{ t('polls', 'Public link ({token})', {token: token }) }}
		</div>
		<slot />
	</div>
</template>

<script>
import { Avatar } from '@nextcloud/vue'

export default {
	name: 'PublicShareItem',

	components: {
		Avatar,
	},

	inheritAttrs: false,

	props: {
		token: {
			type: String,
			default: '',
		},
		type: {
			type: String,
			default: 'user',
		},
	},
}

</script>

<style lang="scss">
.share-item {
	display: flex;
	flex: 1;
	align-items: center;
	max-width: 100%;
}

.share-item__avatar {
	margin: 2px 4px;
}

.share-item__name {
	flex: 1;
	min-width: 50px;
	color: var(--color-text-maxcontrast);
	padding-left: 8px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

</style>
