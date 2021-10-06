<?php

/* Регистрация произвольного типа записи */
add_action( 'init', 'scf_register_casino_pt' );
function scf_register_casino_pt() {
	$labels = array(
		'name'               => 'Казино',
		'singular_name'      => 'Казино',
		'add_new'            => 'Добавить казино',
		'add_new_item'       => 'Добавить новое казино',
		'edit_item'          => 'Редактировать Казино',
		'new_item'           => 'Новое казино',
		'all_items'          => 'Все казино',
		'view_item'          => 'Просмотр казино на сайте',
		'search_items'       => 'Искать казино',
		'not_found'          => 'Казино не найдено.',
		'not_found_in_trash' => 'В корзине нет казино.',
		'menu_name'          => 'Казино'
	);
	$args   = array(
		'labels'        => $labels,
		'public'        => true,
		'show_ui'       => true,
		'has_archive'   => false,
		'menu_icon'     => 'dashicons-admin-post',
		'menu_position' => 8,
		'supports'      => array( 'title', 'editor', 'comments', 'author', 'thumbnail' )
	);
	register_post_type( 'casino', $args );
}
