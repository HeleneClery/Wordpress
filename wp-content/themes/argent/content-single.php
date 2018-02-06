<?php
/**
 * @package Argent
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

	</header><!-- .entry-header -->

	<?php if ( ! post_password_required() && ! is_attachment() && has_post_thumbnail() ) { ?>
		<?php the_post_thumbnail( 'argent-single-thumbnail' ); ?>
	<?php } ?>
	<div class="entry-body">
		<div class="entry-meta">
			<?php argent_posted_on(); ?>
			<?php argent_entry_meta(); ?>
		</div><!-- .entry-meta -->

		<div class="entry-content">
			<?php the_content(); ?>
		</div><!-- .entry-content -->
	</div><!-- .entry-body -->
</article><!-- #post-## -->
