<?php
global $post;
?>
<li >
    <a href="<?php the_permalink(); ?>"><?php esc_attr( the_title() ); ?></a>
    <div><i class="fa fa-calendar-times-o"></i> <?php echo date_i18n(get_option('date_format'), strtotime(get_the_date('F jS, Y'))); ?></div>
</li>