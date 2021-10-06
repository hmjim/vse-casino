<?php

/* включаем поддержку миниатюр */
add_theme_support( 'post-thumbnails' );

/* Автоматический title через хук wp_head() */
add_theme_support( 'title-tag' );

/* Отключаем емоджи */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/* HTML теги в описании категорий, меток */
remove_filter( 'pre_term_description', 'wp_filter_kses' );
remove_filter( 'pre_link_description', 'wp_filter_kses' );
remove_filter( 'pre_link_notes', 'wp_filter_kses' );
remove_filter( 'term_description', 'wp_kses_data' );

/* Меняем длину excerpt */
add_filter( 'excerpt_length', 'new_excerpt_length' );
function new_excerpt_length( $length ) {
	return 40;
}

/* Меняем в excerpt [...] на Читать дальше */
add_filter( 'excerpt_more', 'new_excerpt_more' );
function new_excerpt_more( $more ) {
	global $post;

	return '... <a href="' . get_permalink( $post ) . '">Читать дальше</a>';
}

add_action( 'pre_get_posts', 'global_ppp' );
function global_ppp( $query ) {
	if ( ! is_admin() && $query->is_main_query() ):
		$query->set( 'posts_per_page', - 1 );

		return;
	endif;
}
