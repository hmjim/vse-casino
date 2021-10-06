<div class="sidebar" data-sticky-container>

	<?php
	$i          = 1;
	$casino_top = get_field( 'casino_rating', 'options' );
	?>

	<?php if ( $casino_top ) : ?>
		<div class="widget-casino" data-sticky-for="991">

			<?php foreach ( $casino_top as $casino ) : ?>
				<?php
				$casino_id     = $casino['casino'];

				$casino_link = get_field( 'link', $casino_id );
				$casino_thumb  = get_post( get_post_thumbnail_id( $casino_id ) );
				$casino_rating = get_field( 'rating', $casino_id );
				$casino_bonus  = get_field( 'bonus', $casino_id );
				?>

				<div class="widget-casino-item">
					<a href="<?= $casino_link; ?>" class="widget-casino-img">
						<img src="<?= kama_thumb_src( 'w=100 &h=99 &allow=any', $casino_thumb->guid ); ?>"
						     title="<?= $casino_thumb->post_title; ?>"
						     alt="<?= get_post_meta( $casino_thumb->ID, '_wp_attachment_image_alt', true ); ?>">
					</a>

					<div class="widget-casino-text">
						<div class="widget-casino-rate">Рейтинг - <?= $casino_rating; ?>%</div>
						<a href="<?= $casino_link; ?>" class="btn btn-accent" target="_blank">ИГРАТЬ</a>
						<div class="widget-casino-bonus">Бонус до <?= $casino_bonus; ?></div>
					</div>
				</div>
				<?php $i ++; ?>
			<?php endforeach; ?>

		</div>
	<?php endif; ?>

</div>
