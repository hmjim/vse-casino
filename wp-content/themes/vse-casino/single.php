<?php get_header(); ?>
<?php the_post(); ?>

<div class="page-header">
	<div class="container">

		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>

		<h1>Играть в автомат <?php the_title(); ?> в казино</h1>

		<?php the_field( 'seo_text' ); ?>

	</div>
</div>

<div class="section section-slots-single">
	<div class="container">
		<div class="post">
			<div class="row">
				<div class="main">

					<div class="main-game-frame">
						<iframe src="<?php the_field( 'iframe' ) ?>" width="100%" height="477"></iframe>
						<a href="/goto/money" class="btn btn-accent btn-big btn-fullwidth"><span>Играть на деньги</span></a>
					</div>

					<?php the_content(); ?>
				</div>

				<?php get_sidebar( 'slots-single' ); ?>
			</div>
		</div>

		<?php get_template_part( 'template-parts/top-table-slot' ); ?>

	</div>
</div>

<?php get_footer(); ?>
