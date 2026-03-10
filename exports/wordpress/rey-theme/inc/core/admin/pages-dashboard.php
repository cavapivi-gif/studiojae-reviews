<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">

<div class="rey-adminPage-wrapper rey-dashboard-wrapper wrap">

	<main class="rey-dashboard-main <?php echo ReyTheme_Base::get_purchase_code() ? '--registered' : ''; ?>">
		<?php
		/**
		 * Hook to add boxes into the theme's dasboard.
		 * Example of Dashboard box

			<div class="rey-dashBox">
				<div class="rey-dashBox-inner">
					<h2 class="rey-dashBox-title">This is a box</h2>
					<div class="rey-dashBox-content">
						...
					</div>
				</div>
			</div>
		 */

		 do_action('rey/dashboard/boxes');
		?>
	</main>

</div>
