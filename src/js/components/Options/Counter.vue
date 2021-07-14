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
	<div :class="['counter' , counterStyle ]">
		<div v-if="counterStyle === 'iconStyle'" class="counter--icon">
			<div class="yes">
				<span>{{ option.yes }}</span>
			</div>
			<div v-if="showMaybe" class="maybe">
				<span>{{ option.maybe }}</span>
			</div>
			<div v-if="showNo" class="no">
				<span>{{ option.no }}</span>
			</div>
		</div>

		<div v-if="counterStyle === 'bubbleStyle'" class="counter--bubble">
			<div v-if="showNo" class="no" :style="{flex: option.no }">
				<span />
			</div>

			<div v-if="option.maybe && showMaybe" class="maybe" :style="{flex: option.maybe }">
				<span> {{ option.maybe }} </span>
			</div>

			<div v-if="option.yes" class="yes" :style="{ flex: option.yes }">
				<span> {{ option.yes }} </span>
			</div>
		</div>

		<div v-if="counterStyle === 'barStyle'" class="counter--bar">
			<div v-if="showNo" class="no" :style="{flex: option.no }">
				<span />
			</div>

			<div v-if="option.yes" class="yes" :style="{ flex: option.yes }">
				<span />
			</div>

			<div v-if="option.maybe && showMaybe" class="maybe" :style="{ flex: option.maybe }">
				<span />
			</div>
		</div>
	</div>
</template>

<script>

export default {
	name: 'Counter',

	props: {
		option: {
			type: Object,
			default: undefined,
		},
		counterStyle: {
			type: String,
			default: 'iconStyle',
		},
		showMaybe: {
			type: Boolean,
			default: false,
		},
		showNo: {
			type: Boolean,
			default: false,
		},
	},
}

</script>

<style lang="scss">

.counter {
	display: flex;
	justify-content: space-around;
}

.counter--icon {
	display: flex;
	justify-content: center;
	font-size: 1.1em;

	&> * {
		background-position: 0px 2px;
		padding-left: 23px;
		background-repeat: no-repeat;
		background-size: contain;
		margin: 4px;
	}

	.yes {
		color: var(--color-polls-foreground-yes);
		background-image: var(--icon-polls-yes);
	}
	.no {
		color: var(--color-polls-foreground-no);
		background-image: var(--icon-polls-no);
	}
	.maybe {
		color: var(--color-polls-foreground-maybe);
		background-image: var(--icon-polls-maybe);
	}
}

.counter--bubble {
	display: flex;
	width: 80px;
	flex: 1;
	align-self: center;

	> * {
		text-align: center;
		border-radius: var(--border-radius-pill);
		margin: 2px;
	}

	.yes {
		background-color: var(--color-polls-foreground-yes);
	}

	.maybe {
		background-color: var(--color-polls-foreground-maybe);
	}

	.no {
		background-color: transparent;
	}

}

.list-view .counter {
	flex: 0;
}

.counter--bar {
	display: flex;
	width: 100%;
	flex: 1;
	height: 4px;
	margin-bottom: 4px;

	> * {
		text-align: center;
	}

	.yes {
		background-color: var(--color-polls-foreground-yes);
		order: 1;
	}

	.maybe {
		background-color: var(--color-polls-foreground-maybe);
		order: 2;
	}

	.no {
		background-color: transparent;
		order: 3;
	}

}
</style>
