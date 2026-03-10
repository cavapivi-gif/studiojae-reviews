<script type="text/html" id="tmpl-reySearchPanel">
	<div class="rey-searchItems">
	<# var items = data.items; #>
	<# for (var i = 0; i < items.length; i++) { #>
		<div class="rey-searchItem {{( items[i].default ? '--last' : '' )}}" style="transition-delay: {{i * 0.024}}s " data-id="{{items[i].id}}">
			<a href="{{items[i].permalink}}" class="{{( items[i].default ? 'btn btn-line-active' : '' )}}">
				<# if( items[i].default ) { #>
					{{{items[i].text}}}
					<# } else { #>
						<# if( ! data.only_title && items[i].img ) { #>
							<div class="rey-searchItem-thumbnail"><img src="{{items[i].img}}" alt="{{items[i].text}}"></div>
						<# } #>
						<div class="rey-searchItem-content">
							<?php do_action('reycore/woocommerce/search/results_template/before_content'); ?>
							<div class="rey-searchItem-title">{{{items[i].text}}}</div>
							<# if( ! data.only_title && items[i].price ) { #>
								<div class="rey-searchItem-price">{{{items[i].price}}}</div>
							<# } #>
							<?php do_action('reycore/woocommerce/search/results_template/after_content'); ?>
						</div>
				<# } #>
			</a>
		</div>
	<# } #>
	</div>
</script>
