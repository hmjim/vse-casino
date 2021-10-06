<?php
$text_1 = get_field( 'banner_text_1' );
$text_2 = get_field( 'banner_text_2' );
$text_3 = get_field( 'banner_text_3' );
$btn    = get_field( 'banner_btn' );
?>
<?php if ( $text_1 || $text_2 || $text_3 || $btn ) : ?>
	<div class="banner">
		<div class="container">
			<div class="banner-text-1"><?= $text_1; ?></div>
			<div class="banner-text-2"><?= $text_2; ?></div>
			<div class="banner-text-3"><?= $text_3; ?></div>
			<div class="banner-btn">
				<?= get_field_link( $btn, 'btn btn-accent btn-big' ); ?>
			</div>
		</div>
	</div>
<?php endif; ?>
