<?php get_header(); ?>
<?php the_post(); ?>

<?php
$casino_thumb   = get_post( get_post_thumbnail_id( get_the_ID() ) );
$casino_pays    = get_the_terms( get_the_ID(), 'currency' );
$casino_soft    = get_the_terms( get_the_ID(), 'developer' );
$casino_country = get_the_terms( get_the_ID(), 'country' );
$casino_rating  = get_field( 'rating' );
$casino_bonus   = get_field( 'bonus' );
$casino_site    = get_field( 'link' );
?>

<div class="page-header">
	<div class="container">

		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>

		<h1 class="title">Обзор онлайн-казино <?php the_title(); ?></h1>

		<div class="cas-banner">
			<div class="cas-thumb">
				<a href="<?php echo $casino_site; ?>" target="_blanck">
					<img src="<?php echo kama_thumb_src( 'w=240 &h=230 &allow=any' ); ?>"
						title="<?php echo $casino_thumb->post_title; ?>"
						alt="<?php echo get_post_meta( $casino_thumb->ID, '_wp_attachment_image_alt', true ); ?>"> 
				</a>
			</div>
			<div class="cas-features">
				<div class="cas-features-left">

					<div class="cas-features-item">
						<div class="cas-features-title">Рейтинг:</div>
						<div class="cas-features-rate">
							<div class="cas-features-bar">
								<div class="cas-features-count" style="width: <?= $casino_rating; ?>%;"></div>
							</div>
							<div class="cas-features-text"><?= $casino_rating; ?>%</div>
						</div>
						<div class="cas-features-notice"><?= get_field('rating_text', get_the_ID())?></div>
					</div>

					<div class="cas-features-item">
						<div class="cas-features-title">Приветственный бонус:</div>
						<div class="cas-features-bonus"><?= $casino_bonus; ?></div>
					</div>

					<div class="cas-features-item">
						<div class="cas-features-title">Способы выплаты:</div>
						<ul>
							<?php foreach ( $casino_pays as $item ): ?>
								<?php $logo = get_field( 'logo', $item ); ?>
								<li><img src="<?= kama_thumb_src( 'h=18 &crop=false &allow=any', $logo['url'] ); ?>" title="<?= $logo['title']; ?>" alt="<?= $logo['title']; ?>"></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>

				<div class="cas-features-right">
					<div class="cas-features-item">
						<div class="cas-features-title">Софт игр:</div>
						<ul>
							<?php foreach ( $casino_soft as $item ): ?>
								<?php $logo = get_field( 'logo', $item ); ?>
								<li><img src="<?= kama_thumb_src( 'h=18 &crop=false &allow=any', $logo['url'] ); ?>" title="<?= $logo['title']; ?>" alt="<?= $logo['title']; ?>"></li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="cas-features-item country">
						<div class="cas-features-title">Принимает игроков из:</div>
						<ul>
							<?php foreach ( $casino_country as $item ): ?>
								<?php $logo = get_field( 'logo', $item ); ?>
								<li><img src="<?= kama_thumb_src( 'h=18 &crop=false &allow=any', $logo['url'] ); ?>" title="<?= $logo['title']; ?>" alt="<?= $logo['title']; ?>"></li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="cas-features-item">
						<a href="<?php echo $casino_site; ?>" class="btn btn-accent btn-big" target="_blanck">играть в казино</a>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<?php get_template_part( 'template-parts/anchor-menu' ); ?>

<div class="content">
	<div class="container">
		<div class="main">
			<?php the_content(); ?>

			<?php comments_template(); ?>
		</div>
	</div>
</div>

<?php get_template_part( 'template-parts/casino-banner' ); ?>

<?php get_footer(); ?>
