<?php

/* Register menus */
register_nav_menus(
	array(
		'main'   => 'Главное',
		'bottom' => 'Нижнее',
	)
);

/* Register sidebars */
register_sidebar(
	array(
		'name'          => 'Колонка слева',
		'id'            => "left-sidebar",
		'description'   => 'Обычная колонка в сайдбаре',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => "</div>\n",
		'before_title'  => '<span class="widgettitle">',
		'after_title'   => "</span>\n",
	)
);
