<?php
$page_for_posts = get_option( 'page_for_posts' );

if ( is_home() ) :
	$anchor_menu = get_field( 'anchor_menu', $page_for_posts );
else :
	$anchor_menu = get_field( 'anchor_menu' );
endif;

?>

<?php if ( $anchor_menu ) : ?>
	<div class="section">
		<div class="container">
			<div class="row">
				<div class="anchor-sidebar" data-sticky-container>
					<div class="anchor-menu anchor-section" data-sticky-for="991">
						<ol>
							<?php foreach ( $anchor_menu as $menu ) : ?>
								<li class="go_to"><a href="#<?= $menu['id'] ?>"><?= $menu['menu'] ?></a></li>
							<?php endforeach; ?>
						</ol>
					</div>
				</div>

				<div class="anchor-content content">
					<?php foreach ( $anchor_menu as $menu ) : ?>
						<div id="<?= $menu['id'] ?>" class="anchor-section">
							<?= $menu['section'] ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
