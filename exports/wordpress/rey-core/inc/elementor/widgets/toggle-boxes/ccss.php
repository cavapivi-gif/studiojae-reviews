<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action('reycore/critical_css/before_render', function($ccss){

	$css[] = '.--direction--h .rey-toggleBoxes {
		flex-direction: row;
	}';

	$css[] = '.rey-toggleBoxes {
		display: flex;
		flex-wrap: wrap;
	}';

	$css[] = '.rey-toggleBoxes--default .rey-toggleBox {
		display: inline-flex;
		align-items: center;
	}';

	$css[] = '.rey-toggleBoxes--stacks .rey-toggleBox {
		flex: 1;
	}';

	$css[] = '.rey-toggleBoxes--stacks .rey-toggleBox > a > span {
		display:block;
	}';

	$ccss->add_css($css);

});
