<?php
namespace ReyCore\Elementor;

class Loop_Dynamic_CSS extends \Elementor\Core\DynamicTags\Dynamic_CSS {

	private $post_id_for_data;

	public function __construct( $post_id, $post_id_for_data ) {

		$this->post_id_for_data = $post_id_for_data;

		$post_css_file = \Elementor\Core\Files\CSS\Post::create( $post_id_for_data );

		parent::__construct( $post_id, $post_css_file );
	}

	public function get_post_id_for_data() {
		return $this->post_id_for_data;
	}
}
