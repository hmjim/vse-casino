<div class="sidebar" data-sticky-container>
	<?php global $post;
	$posts = get_posts(
		array(
			'numberposts' => 5,
			'post_type'   => 'post',
			'exclude'     => array( get_the_ID() ),
		)
	);
	?>
	<?php if ( $posts ) : ?>
		<div class="widget-slots" data-sticky-for="991">
			<div class="widget-title">Похожие автоматы</div>
			<div class="widget-slots-wrapper">
				<?php foreach ( $posts as $post ) {
					setup_postdata( $post ); ?>

					<?php
					$thumb     = get_post( get_post_thumbnail_id() );
					$image_alt = get_post_meta( $thumb->ID, '_wp_attachment_image_alt', true );
					?>
					<div class="widget-slot">
						<a href="<?php the_permalink(); ?>" class="widget-slot-link">
							<img src="<?= kama_thumb_src( 'w=240 &h=150 &allow=any' ); ?>" title="<?= $thumb->post_title; ?>" alt="<?= $image_alt; ?>">
							<span class="widget-slot-title"><?php the_title(); ?></span>
						</a>
					</div>
				<?php }
				wp_reset_postdata(); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
