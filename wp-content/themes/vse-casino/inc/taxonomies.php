<?php
// custom taxonomy
add_action( 'init', 'create_casino_taxonomies' );
function create_casino_taxonomies() {
	register_taxonomy( 'currency', array( 'casino' ), array(
		'hierarchical' => true,
		'labels'       => array(
			'name'              => _x( 'Выплаты', 'taxonomy general name' ),
			'singular_name'     => _x( 'Выплата', 'taxonomy singular name' ),
			'search_items'      => __( 'Искать выплаты' ),
			'all_items'         => __( 'Все выплаты' ),
			'parent_item'       => __( 'Выплаты' ),
			'parent_item_colon' => __( 'Выплаты:' ),
			'edit_item'         => __( 'Редактировать выплату' ),
			'update_item'       => __( 'Обновить выплату' ),
			'add_new_item'      => __( 'Добавить выплату' ),
			'new_item_name'     => __( 'Имя выплаты' ),
			'menu_name'         => __( 'Выплаты' ),
		),
		'show_ui'      => true,
		'query_var'    => true,
		'public'       => false,
	) );

	register_taxonomy( 'developer', array( 'casino' ), array(
		'hierarchical' => true,
		'labels'       => array(
			'name'              => _x( 'Разработчики', 'taxonomy general name' ),
			'singular_name'     => _x( 'Разработчик', 'taxonomy singular name' ),
			'search_items'      => __( 'Искать разработчиков' ),
			'all_items'         => __( 'Все разработчики' ),
			'parent_item'       => __( 'Разработчики' ),
			'parent_item_colon' => __( 'Разработчики:' ),
			'edit_item'         => __( 'Редактировать разработчика' ),
			'update_item'       => __( 'Обновить разработчика' ),
			'add_new_item'      => __( 'Добавить разработчика' ),
			'new_item_name'     => __( 'Имя разработчика' ),
			'menu_name'         => __( 'Разработчики' ),
		),
		'show_ui'      => true,
		'query_var'    => true,
		'public'       => false,
	) );

	register_taxonomy( 'country', array( 'casino' ), array(
		'hierarchical' => true,
		'labels'       => array(
			'name'              => _x( 'Принимает игроков из', 'taxonomy general name' ),
			'singular_name'     => _x( 'Страна', 'taxonomy singular name' ),
			'search_items'      => __( 'Искать страну' ),
			'all_items'         => __( 'Все страны' ),
			'parent_item'       => __( 'Страны' ),
			'parent_item_colon' => __( 'Страны:' ),
			'edit_item'         => __( 'Редактировать страну' ),
			'update_item'       => __( 'Обновить страну' ),
			'add_new_item'      => __( 'Добавить страну' ),
			'new_item_name'     => __( 'Имя страны' ),
			'menu_name'         => __( 'Страны' ),
		),
		'show_ui'      => true,
		'query_var'    => true,
		'public'       => false,
	) );
}
