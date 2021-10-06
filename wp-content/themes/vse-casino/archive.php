<?php get_header(); ?>

<div class="page-header">
	<div class="container">

		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>

		<h1><?php single_term_title(); ?></h1>

		<?php the_field( 'top_text' ); ?>

	</div>
</div>

<?php get_template_part( 'template-parts/section-posts' ); ?>

<?php get_footer(); ?>
