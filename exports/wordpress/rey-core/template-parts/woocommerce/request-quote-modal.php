<?php
defined( 'ABSPATH' ) || exit; ?>

<script type="text/template" id="tmpl-rey-request-quote-modal">

	<div class="rey-requestQuote-modal --hidden" data-id="{{data.id}}">

		<?php if( $modal_title = $args['defaults']['title'] ): ?>
			<h3 class="rey-requestQuote-modalTitle"><?php echo $modal_title; ?></h3>
		<?php endif; ?>

		<# if( data.title ){ #>
			<p class="rey-requestQuote-productData">
				<strong class="__title">{{{data.title}}}</strong>
				<# if( data.sku ){ #>
					&nbsp;<strong class="__sku">{{{data.sku}}}</strong>
				<# } #>
			</p>
		<# } #>

		<?php echo $args['form']; ?>
	</div>

</script>
