<?php
namespace ReyCore\Libs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Countdown
{

	const ASSET_HANDLE = 'reycore-countdown';

	public static $settings = [];

	public function __construct($settings = [])
	{

		self::$settings = wp_parse_args($settings, self::get_defaults() );

		do_action('reycore/libs/countdown', $this);

		return $this;
	}

	public static function get_defaults(){
		return [
			'now'           => new \DateTime( "now", wp_timezone() ),
			'to'            => '',
			'labels'        => true,
			'inline'        => false,
			'use_short'     => true,
			'use_icon'      => true,
			'hidden_labels' => [],
			'classes'       => [],
			'text'          => '',
			'strings'       => [
				'm' => [
					'singular' => esc_html_x('month', 'Countdown "month" text', 'rey-core'),
					'plural'   => esc_html_x('months', 'Countdown "months" text', 'rey-core'),
					'abbr'     => esc_html_x('mon.', 'Countdown "month" short text', 'rey-core'),
				],
				'd' => [
					'singular' => esc_html_x('day', 'Countdown "day" text', 'rey-core'),
					'plural'   => esc_html_x('days', 'Countdown "days" text', 'rey-core'),
					'abbr'     => esc_html_x('days', 'Countdown "day" short text', 'rey-core'),
				],
				'h' => [
					'singular' => esc_html_x('hour', 'Countdown "hour" text', 'rey-core'),
					'plural'   => esc_html_x('hours', 'Countdown "hours" text', 'rey-core'),
					'abbr'     => esc_html_x('hours', 'Countdown "hour" short text', 'rey-core'),
				],
				'i' => [
					'singular' => esc_html_x('minute', 'Countdown "minute" text', 'rey-core'),
					'plural'   => esc_html_x('minutes', 'Countdown "minutes" text', 'rey-core'),
					'abbr'     => esc_html_x('min.', 'Countdown "minute" short text', 'rey-core'),
				],
				's' => [
					'singular' => esc_html_x('second', 'Countdown "second" text', 'rey-core'),
					'plural'   => esc_html_x('seconds', 'Countdown "seconds" text', 'rey-core'),
					'abbr'     => esc_html_x('sec.', 'Countdown "seconds" short text', 'rey-core'),
				],
			],
			'icon' => '<svg class="rey-icon" width="100%" height="100%" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M7.72461 0.0374277C7.48398 0.0131834 7.24229 0.0011146 7.00048 0.00122141H6.99322C6.59303 0.00784321 6.27305 0.335943 6.27646 0.736241V3.62164C6.2755 3.71883 6.29526 3.81506 6.33435 3.90402C6.45963 4.20222 6.76808 4.38058 7.08892 4.34053C7.45952 4.28499 7.73145 3.96341 7.72461 3.58885V1.49764C10.4284 1.85255 12.4746 4.11806 12.553 6.844C12.6314 9.56993 10.7189 11.9494 8.04 12.4591C5.36105 12.9688 2.70838 11.458 1.78026 8.89376C0.852143 6.32953 1.92317 3.47083 4.30776 2.14775C4.62475 1.96822 4.7574 1.57988 4.61642 1.24398L4.61557 1.24195C4.53653 1.05088 4.37975 0.902747 4.18451 0.8345C3.98938 0.76636 3.77439 0.78473 3.59357 0.884911C0.576484 2.56599 -0.763363 6.19656 0.43828 9.43451C1.63982 12.6726 5.02377 14.5501 8.40708 13.8559C11.7904 13.1618 14.1617 10.1036 13.9913 6.65399C13.8209 3.20446 11.1598 0.394791 7.72461 0.0374277Z" fill="currentColor"/> <path d="M4.35554 4.67456C4.64679 5.44151 5.59969 7.25235 6.31249 8.02336C6.7507 8.51807 7.49896 8.58429 8.01728 8.17438C8.27659 7.95682 8.43263 7.64047 8.44759 7.30234C8.46243 6.9643 8.33459 6.63546 8.09546 6.39601C7.34997 5.65063 5.45796 4.66302 4.66463 4.36184C4.57577 4.32841 4.47548 4.35041 4.40862 4.41791C4.34187 4.48552 4.32104 4.58602 4.35554 4.67456Z" fill="currentColor"/> </svg>',
		];
	}

	public function set_to($to, $convert = false){

		if( $convert ){
			$to = new \DateTime( $to, wp_timezone() );
		}

		self::$settings['to'] = $to;
	}

	public static function get_evergreen($args){

		$args = wp_parse_args($args, [
			'starting_from' => '',
			'duration'      => '',
			'repeat_count'  => '',
			'now'           => isset(self::$settings['now']) ? self::$settings['now'] : ''
		]);

		if( ! (($duration = absint($args['duration'])) && ($starting_date = $args['starting_from'])) ){
			return;
		}

		$wp_timezone = wp_timezone();
		$initial_start_date = new \DateTime( $starting_date, $wp_timezone );

		$days_since_initial_start_and_now = absint($args['now']->diff($initial_start_date)->format("%a"));
		$past_repeated_cycles = $days_since_initial_start_and_now / $duration;
		$days_since_initial_start_and_latest_cycle_start = absint( floor($past_repeated_cycles) ) * $duration;

		// stop if exceeded the repetitions
		if( ($repeat_count = absint($args['repeat_count'])) && ($repeat_count < $past_repeated_cycles) ){
			return;
		}

		// if the first cycle wasnt exceeded
		if( 0 === absint( floor($past_repeated_cycles) ) ){
			$sale_to_days = $duration;
		}
		else {
			$sale_to_days = $days_since_initial_start_and_latest_cycle_start + $duration;
		}

		return new \DateTime( sprintf( '%s +%d day', $starting_date, $sale_to_days ) , $wp_timezone );
	}

	public static function get_string($item, $type){

		if( ! (isset(self::$settings['strings'][$item][$type]) && ($string = self::$settings['strings'][$item][$type])) ){
			$defaults = self::get_defaults();
			$string = $defaults['strings'][$item][$type];
		}

		return $string;
	}

	public function render($args = []){

		$args = reycore__wp_parse_args($args, self::$settings);

		if( ! ($args['now'] && $args['to']) ){
			return;
		}

		if( $args['now']->getTimestamp() > $args['to']->getTimestamp() ){
			return;
		}

		$output = '';

		$remaining = date_diff($args['now'], $args['to']);

		foreach(['d', 'h', 'i', 's'] as $d){

			if( $remaining->invert ){
				break;
			}

			if( ! isset($remaining->{$d})  ){
				continue;
			}

			if( in_array($d, self::$settings['hidden_labels'], true) ){
				continue;
			}

			$output .= sprintf(' <li class="__item --%1$s"><span class="__number" data-val="%2$s"></span>', $d, $remaining->{$d} );

			if( self::$settings['labels'] ){

				$attrs = [
					'data-singular' => self::get_string($d, 'singular'),
					'data-plural' => self::get_string($d, 'plural'),
				];

				if( $args['inline'] ){
					$attrs['data-inline'] = substr($attrs['data-singular'], 0, 1);
				}

				if( self::$settings['use_short'] ){
					$attrs['data-abbr'] = self::get_string($d, 'abbr');
				}

				$output .= sprintf('<span class="__label" %s></span>', reycore__implode_html_attributes($attrs) );
			}

			$output .= '</li>';
		}

		if( ! $output ){
			return;
		}

		$before = '';

		if( $args['inline'] ){
			// add custom class
			$args['classes'][] = '--inline';
			// define icon
			$icon = self::$settings['use_icon'] ? self::$settings['icon'] : '';
			// add output
			$before .= sprintf('<li class="__icon --no-str">%s</li>', $icon);
		}

		if( $custom_text = $args['text'] ){
			$before .= sprintf('<li class="__title --no-str">%s</li>', $custom_text);
		}

		reycore_assets()->add_styles(self::ASSET_HANDLE);
		reycore_assets()->add_scripts(self::ASSET_HANDLE);

		$w_attributes = [
			'class' => implode(' ', array_merge(['rey-countDown'], $args['classes'])),
			'data-finish' => $args['to']->format('Y/m/d H:i:s P'),
		];

		if( apply_filters('reycore/libs/countdown/use_zero', true) ){
			$w_attributes['data-zero'] = '1';
		}

		return sprintf('<ul %2$s>%1$s</ul>',
			$before . $output,
			reycore__implode_html_attributes($w_attributes)
		);
	}

}
