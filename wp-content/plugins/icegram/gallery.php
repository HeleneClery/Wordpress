<style type="text/css">
	.ig-gallery-wrap .theme-browser .theme{
		border-radius: 5px;
		border: none;
		margin-bottom: 5%;
	    box-shadow: 1px 3px 10px 0 rgba(0,0,0,0.15);
	}
	.ig-gallery-wrap .theme-browser .theme:hover{
		    box-shadow: 1px 3px 10px 0 rgba(0,0,0,0.2);
	}
	.ig-gallery-wrap.wrap > h2:first-child{
		padding-left: 0 !important; 
	}
	.ig-gallery-wrap .theme-browser .theme .theme-screenshot{
		border-radius: 5px;
	}
	.ig-gallery-wrap .theme-browser .theme .theme-screenshot img{
		height: 100%;
	}
	.ig-gallery-wrap .theme-browser .theme .theme-installed{
		width: 2em;
  		padding: 0;
  		height: 2em;
	}
	.ig-gallery-wrap .theme-browser .theme .theme-installed:before{
		font-size: 30px;
		top:-2px;
		left:-4px;
	}
	.expanded .wp-full-overlay-footer.ig-get-pro-footer{
		position: fixed;
	    bottom: 60px;
	    left: 0;
	    height: 24px;
	    background: #ece788;
	    text-align: center;
	    padding-top: 0.2em;
	    border-top: 1px dashed #ddd;
	    border-bottom: 1px dashed #ddd;
	}
	.expanded .wp-full-overlay-footer.ig-get-pro-footer span{
		color: #900101;
	}
	.wp-full-overlay-connect{
		background-color: #fff;
		width: 100%;
		height: 100%;
	}
</style>
<div class="wrap ig-gallery-wrap">
	<h2><?php esc_html_e( 'Icegram design templates' ); ?></h2>
	<div class="ig-gal-description"><?php _e('Here\'s a collection of some ','icegram') ?><strong><?php _e('beautiful, powerful ready-to-use Icegram Campaigns.','icegram') ?></strong></div>
    <div><?php _e('No coding or special skills required. Simply click to ' ,'icegram')?><strong><?php _e(' Use This ','icegram')?></strong><?php _e('and the campaign will automatically appear in your Icegram dashboard.','icegram')?></div>
	<br/>
	<div class="wp-filter hide-if-no-js">
		<div class="filter-count">
			<span class="count theme-count"></span>
		</div>

		<ul class="filter-links">
			<li><a href="#" data-sort="all"><?php _ex( 'All', 'themes' ); ?></a></li>
			<li><a href="#" data-sort="17"><?php _ex( 'Optin', 'themes' ); ?></a></li>
			<li><a href="#" data-sort="19"><?php _ex( 'Offer', 'themes' ); ?></a></li>
			<li><a href="#" data-sort="20"><?php _ex( 'Social', 'themes' ); ?></a></li>
		</ul>

		<!-- <button type="button" class="button drawer-toggle" aria-expanded="false"><?php _e( 'Feature Filter' ); ?></button> -->

		<form class="search-form"></form>


	</div>
	
	<div class="theme-browser">
		<div class="themes wp-clearfix"></div>
	</div>
	<div class="theme-install-overlay wp-full-overlay expanded"></div>
<!-- <div class="theme-overlay"></div> -->
</div><!-- .wrap -->
<script id="tmpl-theme" type="text/template">
	<# if ( data.image ) { #>
		<div class="theme-screenshot">
			<img src="{{ data.image.guid }}" alt="" />
		</div>
	<# } else { #>
		<div class="theme-screenshot blank"></div>
	<# } #>
	<span class="more-details" id="{{ data.id }}-action"><?php _e( 'Preview' ); ?></span>
	<div class="theme-author"><?php printf( __( 'By %s' ), '{{{ data.id }}}' ); ?></div>
</script>
<!-- TODO:: Remove it if not required -->

<script id="tmpl-theme-preview" type="text/template">
	<div class="wp-full-overlay-sidebar">
		<div class="wp-full-overlay-header">
			<a href="#" class="close-full-overlay"><span class="screen-reader-text"><?php _e( 'Close' ); ?></span></a>
			<a href="#" class="previous-theme"><span class="screen-reader-text"><?php _ex( 'Previous', 'Button label for a theme' ); ?></span></a>
			<a href="#" class="next-theme"><span class="screen-reader-text"><?php _ex( 'Next', 'Button label for a theme' ); ?></span></a>
			<a href="?action=fetch_messages&campaign_id={{data.campaign_id}}&gallery_item={{data.slug}}" class="button button-primary theme-install" style="display:none"><?php _e( 'Use This', 'icegram' ); ?></a>
			<a href="https://www.icegram.com/pricing/" target="_blank" class="button button-primary ig-get-pro " style="display:none">
				<# if(data.plan === '3') { #>
					<span><?php _e("Get The Max Plan", 'icegram') ?></span>
				<# } else if(data.plan === '2') { #>
					<span><?php _e("Get The Pro Plan", 'icegram') ?></span>
				<# } #>	
			</a>
		</div>
		<div class="wp-full-overlay-sidebar-content">
			<div class="install-theme-info">
				<h3 class="theme-name">{{ data.title.rendered }}</h3>
				<span class="theme-by"><?php printf( __( 'By %s' ), 'Icegram' ); ?></span>

				<img class="theme-screenshot" src="{{ data.image.guid }}" alt="" />

				<div class="theme-details">
					<!--
					<# if ( data.rating && data.rating > 0 ) { #>
						<div class="theme-rating">
							{{{ data.stars }}}
							<span class="num-ratings">Rating : {{ data.rating }}</span>
						</div>
					<# } else { #>
						<span class="no-rating"><?php _e( 'This theme has not been rated yet.' ); ?></span>
					<# } #>
					<div class="theme-version"><?php printf( __( 'Version: %s' ), '{{ data.version }}' ); ?></div> -->
					<div class="theme-description">{{{ data.description }}}</div>
					<!-- <div class="theme-info">Liked this template? <br/>Here's how you can customize it further </div> -->
					<div class="theme-info" style="padding:0.2em"><?php _e( 'Want to personalize this template to fit your brand?', 'icegram' );?><br/> <a href="https://www.icegram.com/documentation/customize-icegrams-gallery-templates/?utm_source=ig_gallery&utm_medium=ig_inapp_promo&utm_campaign=ig_custom_css" target="_blank" class="" style="margin-top:0.4em"><?php _e( 'Personalize It Now' , 'icegram'); ?></a></div>
				</div>
			</div>
		</div>
		<div class="wp-full-overlay-footer ig-get-pro ig-get-pro-footer " style="display:none">
		    <# if(data.plan === '3') { #>
				<span><?php _e("This template is available in the 'Max Plan'", 'icegram') ?></span>
			<# } else if(data.plan === '2') { #>
				<span><?php _e("This template is available in the 'Pro Plan'", 'icegram') ?></span>
			<# } #>
		</div>
		<div class="wp-full-overlay-footer">
			<button type="button" class="collapse-sidebar button-secondary" aria-expanded="true" aria-label="<?php esc_attr_e( 'Collapse Sidebar' ); ?>">
				<span class="collapse-sidebar-arrow"></span>
				<span class="collapse-sidebar-label"><?php _e( 'Collapse' ); ?></span>
			</button>
		</div>
	</div>
	<div class="wp-full-overlay-main">
		<iframe src="{{ data.link }}?utm_source=ig_inapp&utm_campaign=ig_gallery&utm_medium={{data.campaign_id}}" title="<?php esc_attr_e( 'Preview' ); ?>" />
	</div>
</script>
