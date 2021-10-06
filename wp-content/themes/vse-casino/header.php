<!DOCTYPE html>
<html <?php language_attributes(); ?> class='only'>
<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="format-detection" content="telephone=no">
	<link rel="shortcut icon" type="image/x-icon" href="<?= THEME_IMG; ?>favicon/favicon.ico">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="header">
	<div class="container">
		<div class="row">
			<a href="/" class="logo"><img src="<?= THEME_IMG; ?>logo.png" alt="Vse Casino"></a>

			<span class="menu-toggle"></span>
			<nav class="header-menu">
				<?php wp_nav_menu(
					array(
						'theme_location' => 'main',
						'container'      => false
					)
				); ?>
			</nav>
		</div>
	</div>
</header>