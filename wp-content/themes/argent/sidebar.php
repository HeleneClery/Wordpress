<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package Argent
 */

if ( ! is_active_sidebar( 'sidebar-1' ) && ! is_active_sidebar( 'sidebar-2' ) && ! is_active_sidebar( 'sidebar-3' ) ) {
	return;
}
?>

<div id="secondary" class="footer-widget-area" role="complementary">
	<div class="widget-wrapper">
		<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<div id="widget-area-1" class="widget-area">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div><!-- #widget-area-1 -->
		<?php endif; ?>

		<?php if ( is_active_sidebar( 'sidebar-2' ) ) : ?>
		<div id="widget-area-2" class="widget-area">
			<?php dynamic_sidebar( 'sidebar-2' ); ?>
		</div><!-- #widget-area-2 -->
		<?php endif; ?>

		<?php if ( is_active_sidebar( 'sidebar-3' ) ) : ?>
		<div id="widget-area-3" class="widget-area">
			<?php dynamic_sidebar( 'sidebar-3' ); ?>
		</div><!-- #widget-area-3 -->
		<?php endif; ?>
	</div><!-- .widget-wrapper -->
</div><!-- #secondary -->
