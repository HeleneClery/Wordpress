<?php
/**
 * @package Argent
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( ! post_password_required() && ! is_attachment() && has_post_thumbnail() ) : ?>
		<a class="post-thumbnail" href="<?php the_permalink(); ?>">
		<?php the_post_thumbnail( 'argent-blog-thumbnail' ); ?>
		</a>
	<?php endif; ?>

	<header class="entry-header">
		<?php the_title( sprintf( '<h1 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h1>' ); ?>

		<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-meta">
			<?php argent_posted_on(); ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_excerpt(); ?>

	</div><!-- .entry-content -->
</article><!-- #post-## -->
