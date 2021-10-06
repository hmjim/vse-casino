<?php get_header(); ?>

<div class="page-header">
	<div class="container">

		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>

		<h1><?php single_post_title(); ?></h1>

	</div>
</div>

<?php get_template_part( 'template-parts/section-posts' ); ?>

<?php get_template_part( 'template-parts/anchor-menu' ); ?>

<?php get_footer(); ?>
