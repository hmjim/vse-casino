<a href="#0" class="btn-top"></a>

<footer id="footer" class="footer">
	<div class="container">
		<div class="row">
			<div class="footer-left">
				<a href="/" class="logo"><img src="<?= THEME_IMG; ?>logo.png" alt="Vse Casino"></a>
				<div class="copy">Все права защищены © 2021</div>
			</div>
			<nav class="footer-menu">
				<?php wp_nav_menu(
					array(
						'theme_location' => 'bottom',
						'container'      => false
					)
				); ?>
			</nav>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
