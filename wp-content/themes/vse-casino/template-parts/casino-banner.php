<?php
$casino_id     = get_the_ID();
$casino_thumb  = get_post( get_post_thumbnail_id( $casino_id ) );
$casino_bonus  = get_field( 'bonus', $casino_id );
$casino_site   = get_field( 'link', $casino_id );
?>

<div class="section c-banner">
	<div class="container">
		<div class="main">
			<div class="row">
				<div class="c-banner-logo">
					<img src="<?= kama_thumb_src( 'w=150 &h=100 &allow=any' ); ?>"
					     title="<?= $casino_thumb->post_title; ?>"
					     alt="<?= get_post_meta( $casino_thumb->ID, '_wp_attachment_image_alt', true ); ?>">
				</div>
				<div class="c-banner-bonus"><span>Бонус</span><span><?= $casino_bonus; ?></span></div>
				<div class="c-banner-btn">
					<a href="<?= $casino_site; ?>" class="btn btn-accent btn-big" target="_blanck">Получить бонус</a>
					<a href="/" class="btn btn-light">Вернуться в Топ казино</a>
				</div>
			</div>
		</div>
	</div>
</div>
