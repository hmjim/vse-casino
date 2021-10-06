<?php

/* правильный способ подключить стили и скрипты */
function scf_theme_scripts() {

	// Подключаем стили
	wp_enqueue_style( 'font-style', 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap&subset=cyrillic' );
	if ( is_singular( array( 'post', 'casino' ) ) ) :
		wp_enqueue_style( 'swiper-style', THEME_CSS . 'swiper.min.css' );
	endif;
	wp_enqueue_style( 'main-style', THEME_CSS . 'main.css' );
	wp_enqueue_style( 'wp-style', get_stylesheet_uri() );

	// Подключаем скрипты
	wp_enqueue_script( 'sticky-scr', THEME_JS . 'sticky.min.js', array( 'jquery' ), '1.0.0' );
	if ( is_singular( array( 'post', 'casino' ) ) ) :
		wp_enqueue_script( 'swiper-scr', THEME_JS . 'swiper.min.js', array( 'jquery' ), '1.0.0' );
	endif;
	wp_enqueue_script( 'main-scr', THEME_JS . 'main.js', array( 'jquery' ), '1.0.0' );
}

add_action( 'wp_enqueue_scripts', 'scf_theme_scripts' );

