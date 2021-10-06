<?php get_header(); ?>
<?php the_post(); ?>

<div class="page-header">
	<div class="container">

		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>

		<h1><?php the_title(); ?></h1>

	</div>
</div>

<div class="content">
	<div class="container">
		<div class="main">
			<?php the_content(); ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>
