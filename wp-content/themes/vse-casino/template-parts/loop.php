<?php
$thumb     = get_post( get_post_thumbnail_id() );
$image_alt = get_post_meta( $thumb->ID, '_wp_attachment_image_alt', true );
?>
<a href="<?php the_permalink(); ?>" class="slots-loop-item">
	<img src="<?php echo kama_thumb_src( "w=262 &h=170 &allow=any" ); ?>" title="Играть в <?php the_title(); ?> на реальные деньги" alt="<?php the_title(); ?>">
	<div class="slots-loop-item-title"> <?php the_title(); ?></div>
</a>
