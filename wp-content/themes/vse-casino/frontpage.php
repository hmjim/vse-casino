<?php
/*
Template Name: Главная
*/

?>
<?php get_header(); ?>

<?php get_template_part( 'template-parts/banner' ); ?>

<?php if ( get_field('main_title_show' ) ) : ?>
<div class="section">
	<div class="container">
		<h1><?= get_field('main_title'); ?></h1>
	</div>
</div>
<?php endif; ?>

<div class="section">
	<div class="container">
		<?php get_template_part( 'template-parts/top-table' ); ?>
	</div>
</div>

<?php get_template_part( 'template-parts/anchor-menu' ); ?>

<?php get_footer(); ?>
