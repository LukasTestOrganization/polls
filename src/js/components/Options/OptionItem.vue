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
	<Component :is="tag" class="option-item" :class="{ draggable: isDraggable, 'date-box': show === 'dateBox' }">
		<div v-if="isDraggable" class="option-item__handle icon icon-handle" />

		<slot name="icon" />

		<div v-if="show === 'textBox'" v-tooltip.auto="optionTooltip" class="option-item__option--text">
			{{ optionText }}
		</div>

		<div v-if="show === 'dateBox'" v-tooltip.auto="dateLocalFormatUTC" class="option-item__option--datebox">
			<div class="event-date">
				<div class="event-from">
					<div class="month">
						{{ event.from.month }}
					</div>
					<div class="day">
						{{ event.from.day }}
					</div>
					<div class="dow">
						{{ event.from.dow }}
					</div>
					<div v-if="!event.dayLong" class="time">
						{{ event.from.time }}
						<span v-if="!event.dayLong && option.duration && event.to.sameDay">
							- {{ event.to.time }}
						</span>
					</div>
				</div>
				<div v-if="option.duration && !event.to.sameDay" class="devider">
					-
				</div>
				<div v-if="option.duration && !event.to.sameDay" class="event-to">
					<div class="month">
						{{ event.to.month }}
					</div>
					<div class="day">
						{{ event.to.day }}
					</div>
					<div class="dow">
						{{ event.to.dow }}
					</div>
					<div v-if=" !event.dayLong" class="time">
						{{ event.to.time }}
					</div>
				</div>
			</div>

			<!-- <div class="event-time">
				<div v-if="!event.dayLong" class="time">
					{{ event.from.time }}
				</div>
				<div v-if="option.duration && !event.dayLong" class="time">
					{{ event.to.time }}
				</div>
			</div> -->
		</div>

		<slot name="actions" />
	</Component>
</template>

<script>
import { mapState } from 'vuex'
import moment from '@nextcloud/moment'
export default {
	name: 'OptionItem',

	props: {
		draggable: {
			type: Boolean,
			default: false,
		},
		option: {
			type: Object,
			required: true,
		},
		tag: {
			type: String,
			default: 'div',
		},
		display: {
			type: String,
			default: 'textBox',
		},
	},
	computed: {
		...mapState({
			poll: (state) => state.poll,
		}),

		isDraggable() {
			return this.draggable
		},

		event() {
			const from = moment.unix(this.option.timestamp)
			const to = moment.unix(this.option.timestamp + Math.max(0, this.option.duration))

			// does the event start at 00:00 local time and
			// is the duration divisable through 24 hours without rest
			// then we have a day long event (one or multiple days)
			// In this case we want to suppress the display of any time information
			const dayLongEvent = from.unix() === moment(from).startOf('day').unix() && this.option.duration && this.option.duration % 86400 === 0
			const dayModifier = dayLongEvent ? 1 : 0
			// modified to date, in case of day long events, a second gets substracted
			// to set the begin of the to day to the end of the previous date
			const toModified = moment(to).subtract(dayModifier, 'days')

			if (this.poll.type !== 'datePoll') {
				return {}
			}
			return {
				from: {
					month: from.format('MMM [ \']YY'),
					day: from.format('D'),
					dow: from.format('ddd'),
					time: from.format('LT'),
					date: from.format('ll'),
					dateTime: from.format('llll'),
					utc: moment(from).utc().format('llll'),
				},
				to: {
					month: toModified.format('MMM'),
					day: toModified.format('D'),
					dow: toModified.format('ddd'),
					time: to.format('LT'),
					date: toModified.format('ll'),
					dateTime: to.format('llll'),
					utc: moment(to).utc().format('llll'),
					sameDay: from.format('L') === toModified.format('L'),
				},
				dayLong: dayLongEvent,
			}

		},

		dateLocalFormat() {
			if (this.poll.type !== 'datePoll') {
				return {}
			} else if (this.option.duration === 0) {
				return this.event.from.dateTime
			} else if (this.event.dayLong && this.event.to.sameDay) {
				return this.event.from.date
			} else if (this.event.dayLong && !this.event.to.sameDay) {
				return this.event.from.date + ' - ' + this.event.to.date
			} else if (this.event.to.sameDay) {
				return this.event.from.dateTime + ' - ' + this.event.to.time
			}
			return this.event.from.dateTime + ' - ' + this.event.to.dateTime

		},

		dateLocalFormatUTC() {
			if (this.option.duration) {
				return this.event.from.utc + ' - ' + this.event.to.utc + ' UTC'
			}
			return this.event.from.utc + ' UTC'

		},

		optionTooltip() {
			if (this.poll.type === 'datePoll') {
				return this.dateLocalFormatUTC
			}
			return this.option.pollOptionText

		},

		optionText() {
			if (this.poll.type === 'datePoll') {
				return this.dateLocalFormat
			}
			return this.option.pollOptionText

		},

		show() {
			if (this.poll.type === 'datePoll' && this.display === 'dateBox') {
				return 'dateBox'
			}
			return 'textBox'

		},
	},
}
</script>

<style lang="scss">

	.option-item {
		display: flex;
		align-items: center;
		flex: 1;
		position: relative;
		&.date-box {
			// flex: 1;
			align-items: stretch;
			flex-direction: column;
		}
	}

	.devider {
		align-self: center;
		color: var(--color-text-lighter);
	}

	.option-item__option--datebox {
		display: flex;
		// flex-direction: column;
		// padding: 0 2px;
		align-items: stretch;
		justify-content: flex-start;
		text-align: center;
		hyphens: auto;
	}

	.event-date {
		display: flex;
		flex: 1;
		flex-direction: row;
		align-items: stretch;
		justify-content: center;
		background-color: var(--color-primary-light);

		.event-from, .event-to {
			display: flex;
			flex-direction: column;
			align-items: stretch;
			flex: 1;
			// flex-direction: column;
			// align-items: center;
			// padding-bottom: 8px;
			// flex: 0;

			.month, .dow, .time {
				white-space: pre;
				font-size: 1.1em;
				padding: 0 4px;
				color: var(--color-text-lighter);
			}
			.day {
				font-size: 1.4em;
				margin: 5px 0 5px 0;
				padding: 0 4px;
			}

			.time {
				font-size: 0.8em;
				padding: 0 4px;
			}
		}
	}

	.event-time {
		display: flex;
		flex-direction: column;
		align-items: center;
		margin-top: 8px;
		.time-to {
			font-size: 0.8em;
		}
	}

	[class*='option-item__option'] {
		flex: 1;
		opacity: 1;
		white-space: normal;
		// padding-right: 4px;
	}

	.option-item__option--text {
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.draggable, .draggable [class*='option-item__option']  {
		cursor: grab;
		&:active {
			cursor: grabbing;
			cursor: -moz-grabbing;
			cursor: -webkit-grabbing;
		}
		.option-item__handle {
			visibility: hidden;
		}
		&:hover > .option-item__handle {
			visibility: visible;
		}

	}

	.option-item__rank {
		flex: 0 0;
		justify-content: flex-end;
		padding-right: 8px;
	}

	.option-item__handle {
		margin-right: 8px;
	}

</style>
