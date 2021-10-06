<div class="c-table">
	<div class="c-thead">
		<div class="c-tr">
			<div class="c-td c-pos">№</div>
			<div class="c-td c-logo">Онлайн казино</div>
			<div class="c-td c-rate">Рейтинг</div>
			<div class="c-td c-bonus">Бонус</div>
			<div class="c-td c-nav">Сайт</div>
		</div>
	</div>
	<div class="c-tbody">
		<?php
		$i          = 1;
		$casino_top = get_field( 'casino_rating', 'options' );
		?>
		<?php foreach ( $casino_top as $casino ) : ?>
			<?php

			if($i <= 5):
			$casino_id     = $casino['casino'];
			$casino_link   = get_post_permalink( $casino_id );
			$casino_thumb  = get_post( get_post_thumbnail_id( $casino_id ) );
			$casino_rating = get_field( 'rating', $casino_id );
			$casino_bonus  = get_field( 'bonus', $casino_id );
			$casino_site   = get_field( 'link', $casino_id );
			?>
			<div class="c-tr">
				<div class="c-td c-pos"><?= $i; ?></div>
				<div class="c-td c-logo">
					<a href="<?= $casino_link; ?>" class="c-logo-img">
						<img src="<?= kama_thumb_src( 'w=140 &h=140 &allow=any', $casino_thumb->guid ); ?>"
						     title="<?= $casino_thumb->post_title; ?>"
						     alt="<?= get_post_meta( $casino_thumb->ID, '_wp_attachment_image_alt', true ); ?>">
					</a>
					<div class="c-logo-title"><?= get_the_title( $casino_id ); ?></div>
				</div>
				<div class="c-td c-rate">
					<div class="c-rate-text"><span class="c-rate-title">Рейтинг -</span> <?= $casino_rating; ?>%</div>
					<div class="c-rate-bar">
						<div class="c-rate-count" style="width: <?= $casino_rating; ?>%;"></div>
					</div>
				</div>
				<div class="c-td c-bonus">Депозитный бонус <br>до <?= $casino_bonus; ?></div>
				<div class="c-td c-nav">
					<a href="<?= $casino_link; ?>" class="btn btn-accent">Обзор</a>
					<a href="<?= $casino_site; ?>" class="btn btn-light" title="Играть в казино" target="_blank">Играть</a>
				</div>
			</div>
			<?php $i ++; endif; endforeach; ?>
	</div>
</div>
