<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Date extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-date',
			'title'      => esc_html__( 'Post Date', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Date type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'post_date',
				'options' => [
					'post_date' => esc_html__('Published', 'rey-core'),
					'post_modified' => esc_html__('Modified', 'rey-core')
				],
			]
		);

		$formats = array_unique( apply_filters( 'date_formats', [ __( 'F j, Y' ), 'Y-m-d', 'm/d/Y', 'd/m/Y' ] ) );

		$this->add_control(
			'format',
			[
				'label' => esc_html__( 'Format', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => get_option( 'date_format' ),
				'options' => array_merge(array_combine($formats, $formats), [
					'custom'  => esc_html__( 'Custom', 'rey-core' ),
				]),
			]
		);

		$this->add_control(
			'custom_format',
			[
				'label' => esc_html__( 'Custom Format', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => get_option( 'date_format' ),
				'placeholder' => esc_html__( 'eg: F j, Y', 'rey-core' ),
				'description' => sprintf('<a href="https://wordpress.org/support/article/formatting-date-and-time/"></a>', esc_html__('Documentation on date and time formatting', 'rey-core')),
				'condition' => [
					'format' => 'custom',
				],
			]
		);
	}

	public function render()
	{
		$settings = $this->get_settings();

		$date = ( ($post = get_post()) && isset($post->{$settings['type']}) && ($d = $post->{$settings['type']}) ) ? $d : '';

		if( ! $date ){
			return;
		}

		$date_object = new \DateTime( $date, wp_timezone() );

		$format = $settings['format'];

		if( 'custom' === $format && ($custom_format = $settings['custom_format']) ){
			$format = $custom_format;
		}

		echo $date_object->format($format);

	}

}
