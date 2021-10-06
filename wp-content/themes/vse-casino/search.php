<?php get_header(); ?>

<div class="page-header">
	<div class="container">

		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>

		<h1><?php printf( 'Поиск по строке: %s', get_search_query() ); ?></h1>

	</div>
</div>

<?php get_template_part( 'template-parts/section-posts' ); ?>

<?php get_footer(); ?>


