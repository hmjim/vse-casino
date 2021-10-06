<?php

add_shortcode( 'screen', 'add_screens' );
function add_screens() {
	$html = '';
	if ( have_rows( 'screens' ) ):
		$html .= '<div class="screens-box">';
		$html .= '<div class="screens-carousel swiper-container">';
		$html .= '<div class="swiper-wrapper">';
		while ( have_rows( 'screens' ) ): the_row();
			$thumb_id  = get_sub_field( 'image' );
			$thumb     = get_post( $thumb_id );
			$image_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
			$link      = wp_get_attachment_image_url( $thumb_id );
			$html      .= '<div class="swiper-slide">';
			$html      .= '<a href="' . $link . '" class="cboxElement">';
			$html      .= '<img src="' . kama_thumb_src( "w=249 &h=162 &allow=any", $link ) . '" title="' . $thumb->post_title . '" alt="' . $image_alt . '" class="colorbox-99991"></a></div>';
		endwhile;
		$html .= '</div>';
		$html .= '<div class="screens-pagination"></div>';
		$html .= '</div>';
		$html .= '</div>';
	endif;

	return $html;
}
