<?php

class Kama_Thumbnail {

	static $opt_name = 'kama_thumbnail';

	static $opt_pagename = 'media'; // kama_thumb on multisite

	static $opt;

	private $skip_setting_page = false;

	function __get( $name ){
		if( $name === 'opt' )
			return self::$opt;
	}

	function __construct(){

		if( $this->skip_setting_page = has_filter( 'kama_thumb_def_options' ) ){
			$opts = array();
		}
		else {
			if( is_multisite() ){
				$opts = get_site_option( self::$opt_name, array() );
				self::$opt_pagename = 'kama_thumb';
			}
			else
				$opts = get_option( self::$opt_name, array() );
		}

		self::$opt = (object) array_merge( self::def_options(), $opts );

		// дополним опции (ниже определения опций)
		if( ! self::$opt->no_photo_url )     self::$opt->no_photo_url     = KT_URL .'no_photo.jpg';
		if( ! self::$opt->cache_folder )     self::$opt->cache_folder     = str_replace('\\', '/', WP_CONTENT_DIR . '/cache/thumb');
		if( ! self::$opt->cache_folder_url ) self::$opt->cache_folder_url = content_url() .'/cache/thumb';

		self::$opt->cache_folder     = untrailingslashit( self::$opt->cache_folder );
		self::$opt->cache_folder_url = untrailingslashit( self::$opt->cache_folder_url );

		// allow_hosts
		$ah = & self::$opt->allow_hosts;
		if( $ah && ! is_array($ah) ){
			$ah = preg_split('/[\s,]+/s', trim( $ah ) ); // сделаем массив
			foreach( $ah as & $host )
				$host = str_replace( 'www.', '', $host );
		}
		else
			$ah = [];

		$this->wp_init();

	}

	function wp_init(){

		if( is_admin() ){

			add_action( 'admin_menu', array( $this, 'cache_clear_handler' ) );

			add_filter( 'save_post', array( $this, 'clear_post_meta' ) );

			// Включает страницу опций только если не были переопределных дефолтные опции через хук `kama_thumb_def_options`
			if( ! defined('DOING_AJAX') && ! $this->skip_setting_page ){

				add_action( (is_multisite() ? 'network_admin_menu' : 'admin_menu'), array( $this, 'admin_options' ) );

				// ссылка на настойки со страницы плагинов
				add_filter( 'plugin_action_links', array( $this, 'setting_page_link' ), 10, 2 );

				// ловля обновления опций
				if( is_multisite() )
					add_action( 'network_admin_edit_'.'kt_opt_up', array( $this, 'network_options_update' ) );

			}

		}

		if( self::$opt->use_in_content ){
			add_filter( 'the_content', array( $this, 'replece_in_content' ) );
			add_filter( 'the_content_rss', array( $this, 'replece_in_content' ) );
		}

		do_action( 'kama_thumb_inited', self::$opt );

	}

	static function def_options(){

		// позволяет изменить дефолтные опции, если хук используется, то страница опций плагина автоматически отключается
		return apply_filters( 'kama_thumb_def_options', [
			'meta_key'          => 'photo_URL', // называние мета поля записи.
			'cache_folder'      => '',          // полный путь до папки миниатюр.
			'cache_folder_url'  => '',          // URL до папки миниатюр.
			'no_photo_url'      => '',          // УРЛ на заглушку.
			'use_in_content'    => 'mini',      // искать ли класс mini у картинок в тексте, чтобы изменить их размер.
			'no_stub'           => false,       // не выводить картинку-заглушку.
			'auto_clear'        => false,       // очищать ли кэш каждые Х дней.
			'auto_clear_days'   => 7,           // каждые сколько дней очищать кэш.
			'rise_small'        => true,        // увеличить создаваемую миниатюру (ширину/высоту), если её размер меньше указанного размера.
			'quality'           => 90,          // качество создаваемых миниатюр.
			'allow_hosts'       => '',          // доступные хосты, кроме родного, через запятую. 'any' - любые хосты.
			'debug'             => 0,           // режим дебаг (для разработчиков).
		] );
	}

	## Функции поиска и замены в посте
	function replece_in_content( $content ){
		$miniclass = (self::$opt->use_in_content == 1) ? 'mini' : self::$opt->use_in_content;

		if( false !== strpos( $content, '<img ') && false !== strpos( $content, $miniclass) ){
			$img_ex = '<img([^>]*class=["\'][^\'"]*(?<=[\s\'"])'. $miniclass .'(?=[\s\'"])[^\'"]*[\'"][^>]*)>';
			// разделение ускоряет поиск почти в 10 раз
			$content = preg_replace_callback("~(<a[^>]+>\s*)$img_ex|$img_ex~", array( $this, '_replece_in_content'), $content );
		}

		return $content;
	}
	function _replece_in_content( $m ){

		$a_prefix = $m[1];
		$is_a_img = '<a' === substr( $a_prefix, 0, 2);
		$attr     = $is_a_img ? $m[2] : $m[3];

		$attr = trim( $attr, '/ ');

		// get src="xxx"
		preg_match('~src=[\'"]([^\'"]+)[\'"]~', $attr, $match_src );
		$src = $match_src[1];
		$attr = str_replace( $match_src[0], '', $attr );

		// make args from attrs
		$args = preg_split('~ *(?<!=)["\'] *~', $attr );
		$args = array_filter( $args );

		$_args = array();
		foreach( $args as $val ){
			list( $k, $v ) = preg_split('~=[\'"]~', $val );
			$_args[ $k ] = $v;
		}
		$args = $_args;

		// parse srcset if set
		if( isset($args['srcset']) ){
			$srcsets = array_map('trim', explode(',', $args['srcset']) );
			$_cursize = 0;
			foreach( $srcsets as $_src ){
				preg_match('/ ([0-9]+[a-z]+)$/', $_src, $mm );
				$size = $mm[1];
				$_src = str_replace( $mm[0], '', $_src );

				// retina
				if( $size === '2x' ){
					$src = $_src;
					break;
				}

				$size = intval($size);
				if( $size > $_cursize )
					$src = $_src;

				$_cursize = $size;
			}

			unset( $args['srcset'] );
		}

		//print_r($args + [$src]); echo "\n\n\n\n";
		$kt = new Kama_Make_Thumb( $args, $src );

		return $is_a_img ? $a_prefix . $kt->img() : $kt->a_img();
	}

	## Очищает произвольное поле со ссылкой при обновлении поста, чтобы создать его снова. Только если метаполе у записи существует.
	function clear_post_meta( $post_id ){
		global $wpdb;
		if( $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, self::$opt->meta_key ) ) )
			update_post_meta( $post_id, self::$opt->meta_key, '' );
	}


	### ADMIN PART -----------------------------------

	static function uninstall(){

		$kt = new Kama_Thumbnail();

		$kt->_clear_cache();
		$kt->_delete_meta();

		delete_option( self::$opt_name );

		@ rmdir( $kt->opt->cache_folder );
	}

	## для вывода сообещний в админке
	static function show_message( $text = '', $class = 'updated' ){

		add_action( 'admin_notices', function() use ( $text, $class ){
			echo '<div id="message" class="'. $class .' notice is-dismissible"><p>'. $text .'</p></div>';
		} );
	}

	function admin_options(){
		// для мультисайта создается отдельная страница в настройках сети
		if( is_multisite() )
			$hook = add_submenu_page( 'settings.php', 'Kama Thumbnail', 'Kama Thumbnail', 'manage_network_options', self::$opt_pagename, array( $this, '_network_options_page') );

		// Добавляем блок опций на базовую страницу "Чтение"
		add_settings_section( 'kama_thumbnail', __('Kama Thumbnail Settings','kama-thumbnail'), '', self::$opt_pagename );

		// Добавляем поля опций. Указываем название, описание,
		// функцию выводящую html код поля опции.
		add_settings_field( 'kt_options_field',
			'
			<p><a class="button" target="_blank" href="'. add_query_arg('kt_clear', 'clear_cache_stub') .'">'. __('Clear nophoto Cache','kama-thumbnail') .'</a></p>
			<p><a class="button" target="_blank" href="'. add_query_arg('kt_clear', 'clear_cache') .'">'. __('Clear all images','kama-thumbnail') .'</a></p>
			<p><a class="button" target="_blank" href="'. add_query_arg('kt_clear', 'delete_meta') .'">'. __('Remove Releted Custom Fields','kama-thumbnail') .'</a></p>
			<p><a class="button" target="_blank" href="'. add_query_arg('kt_clear', 'clear_all_data') .'">'. __('Remove all data','kama-thumbnail') .'</a></p>
			',
			array( $this, '_options_field'),
			self::$opt_pagename, // страница
			'kama_thumbnail' // секция
		);

		// Регистрируем опции, чтобы они сохранялись при отправке
		// $_POST параметров и чтобы callback функции опций выводили их значение.
		register_setting( self::$opt_pagename, self::$opt_name, array( $this, 'sanitize_options') );
	}

	function _network_options_page(){
		echo '<form method="POST" action="edit.php?action=kt_opt_up" style="max-width:900px;">';

		wp_nonce_field( self::$opt_pagename ); // settings_fields() не подходит для мультисайта...

		do_settings_sections( self::$opt_pagename );

		submit_button();

		echo '</form>';
	}

	function _options_field(){

		$opt_name = self::$opt_name;

		$opts = is_multisite() ? get_site_option( $opt_name ) : get_option( $opt_name );
		$opt  = (object) array_merge( self::def_options(), (array) $opts );

		$def_opt = (object) self::def_options();

		$elems = [
			'cache_folder' =>
				'<input type="text" name="'. $opt_name .'[cache_folder]" value="'. $opt->cache_folder .'" style="width:80%;" placeholder="'. self::$opt->cache_folder .'">'.
				'<p class="description">'. __('Full path to the cache folder with 755 rights or above.','kama-thumbnail') .'</p>',

			'cache_folder_url' =>
				'<input type="text" name="'. $opt_name .'[cache_folder_url]" value="'. $opt->cache_folder_url .'" style="width:80%;" placeholder="'. self::$opt->cache_folder_url .'">
				<p class="description">'. __('URL of cache folder.','kama-thumbnail') .'</p>',

			'no_photo_url' =>
				'<input type="text" name="'. $opt_name .'[no_photo_url]" value="'. $opt->no_photo_url .'" style="width:80%;" placeholder="'. self::$opt->no_photo_url .'">
				<p class="description">'. __('URL of stub image.','kama-thumbnail') .'</p>',

			'meta_key' =>
				'<input type="text" name="'. $opt_name .'[meta_key]" value="'. $opt->meta_key .'" class="regular-text">
				<p class="description">'. __('Custom field key, where the thumb URL will be. Default:','kama-thumbnail') .' <code>'. $def_opt->meta_key .'</code></p>',

			'allow_hosts' =>
				'<textarea name="'. $opt_name .'[allow_hosts]" style="width:350px;height:45px;">'. esc_textarea($opt->allow_hosts) .'</textarea>
				<p class="description">'. __('Hosts from which thumbs can be created. One per line: <i>sub.mysite.com</i>. Specify <code>any</code>, to use any hosts.','kama-thumbnail') .'</p>',

			'quality' =>
				'<input type="text" name="'. $opt_name .'[quality]" value="'. $opt->quality .'" style="width:50px;">
				<p class="description" style="display:inline-block;">'. __('Quality of creating thumbs from 0 to 100. Default:','kama-thumbnail') .' <code>'. $def_opt->quality .'</code></p>',

			'no_stub' => '
				<label>
					<input type="hidden" name="'. $opt_name .'[no_stub]" value="0">
					<input type="checkbox" name="'. $opt_name .'[no_stub]" value="1" '. checked(1, @ $opt->no_stub, 0) .'>
					'. __('Don\'t show nophoto image.','kama-thumbnail') .'
				</label>',

			'auto_clear' => '
				<label>
					<input type="hidden" name="'. $opt_name .'[auto_clear]" value="0">
					<input type="checkbox" name="'. $opt_name .'[auto_clear]" value="1" '. checked(1, @ $opt->auto_clear, 0) .'>
					'. sprintf(
						__('Clear all cache automaticaly every %s days.','kama-thumbnail'),
						'<input type="number" name="'. $opt_name .'[auto_clear_days]" value="'. @ $opt->auto_clear_days .'" style="width:50px;">'
					) .'
				</label>',

			'rise_small' => '
				<label>
					<input type="hidden" name="'. $opt_name .'[rise_small]" value="0">
					<input type="checkbox" name="'. $opt_name .'[rise_small]" value="1" '. checked(1, @ $opt->rise_small, 0) .'>
					'. __('Increase the thumbnail you create (width/height) if it is smaller than the specified size.','kama-thumbnail') .'
				</label>',

			'use_in_content' => '
				<input type="text" name="'. $opt_name .'[use_in_content]" value="'.( isset($opt->use_in_content) ? esc_attr($opt->use_in_content) : 'mini' ).'">
				<p class="description">'. sprintf( __('Find specified here class of IMG tag in content and make thumb from found image by it sizes. Leave this field empty to disable this function. Default: %s','kama-thumbnail'), '<code>mini</code>' ) .'</p>',

		];

		$elems = apply_filters( 'kama_thumb_options_field_elems', $elems, $opt_name, $opt, $def_opt );

		$elems['debug'] = '
			<label>
				<input type="hidden" name="'. $opt_name .'[debug]" value="0">
				<input type="checkbox" name="'. $opt_name .'[debug]" value="1" '. checked(1, @ $opt->debug, 0) .'>
				'. __('Debug mode. Recreates thumbs all time. Works with WP_DEBUG enabled only!','kama-thumbnail') .'
			</label>';

		echo '
		<style>
			.ktumb-line{ padding:.5em 0; }
		</style>
		<div class="ktumb-line">'. implode( '</div><div class="ktumb-line">', $elems ) .'</div>';

	}

	## update options from network settings.php
	function network_options_update(){
		// nonce check
		check_admin_referer( self::$opt_pagename );

		$new_opts = wp_unslash( $_POST['kama_thumbnail'] );
		//$new_opts = self::sanitize_options( $new_opts ); // сработает автоматом из register_setting() ...

		update_site_option( self::$opt_name, $new_opts );

		wp_redirect( add_query_arg( 'updated', 'true', network_admin_url( 'settings.php?page='. self::$opt_pagename  ) ) );
		exit();
	}

	## sanitize options
	function sanitize_options( $opts ){
		$defopt = self::def_options();

		foreach( $opts as $key => & $val ){
			if( $key === 'allow_hosts' ){
				$ah = preg_split( '/[\s,]+/s', trim( $val ) ); // сделаем массив

				foreach( $ah as & $host ){
					$host = sanitize_text_field( $host );
					$host = str_replace( 'www.', '', $host );
				}

				$val = implode( "\n", $ah );
			}
			elseif( $key === 'meta_key' && ! $val )
				$val = $defopt['meta_key'];
			else
				$val = sanitize_text_field( $val );
		}

		return $opts;
	}

	function setting_page_link( $actions, $plugin_file ){
		if( false === strpos( $plugin_file, basename(KT_PATH) ) ) return $actions;

		$settings_link = '<a href="'. admin_url('options-media.php') .'">'. __('Settings','kama-thumbnail') .'</a>';
		array_unshift( $actions, $settings_link );

		return $actions;
	}
	### / ADMIN PART -----------------------------------


	### CLEAR CACHE -----------------------------------
	function cache_clear_handler(){
		if( isset($_GET['kt_clear']) && current_user_can('manage_options') ){
			$this->force_clear( $_GET['kt_clear'] );

			ob_start();
			do_action('admin_notices');
			$msg = ob_get_clean();

			wp_die( $msg );
		}

		$this->smart_clear( 'stub' );

		if( ! empty(self::$opt->auto_clear) )
			$this->smart_clear();
	}

	## очистка кэша с проверкой
	function smart_clear( $type = '' ){
		$_stub       = ($type === 'stub');
		$cache_dir   = self::$opt->cache_folder;
		$expire_file = $cache_dir .'/'. ( $_stub ? 'expire_stub' : 'expire' );

		if( ! is_dir($cache_dir) )
			return;

		$expire = $cleared = 0;
		if( file_exists($expire_file) )
			$expire = (int) file_get_contents( $expire_file );

		if( $expire < time() )
			$cleared = $this->_clear_cache( $_stub ? 'only_stub' : '' );

		if( $cleared || ! $expire )
			@ file_put_contents( $expire_file, time() + ($_stub ? DAY_IN_SECONDS : self::$opt->auto_clear_days * DAY_IN_SECONDS) );
	}

	## ?kt_clear=clear_cache - очистит кеш картинок ?kt_clear=delete_meta - удалит произвольные поля
	function force_clear( $type ){
		switch( $type ){
			case 'clear_cache_stub':
				$this->_clear_cache('only_stub');
				break;
			case 'clear_cache':
				$this->_clear_cache();
				break;
			case 'delete_meta':
				$this->_delete_meta();
				break;
			case 'clear_all_data':
				$this->_clear_cache();
				$this->_delete_meta();
				break;
		}
	}

	## непосредственный код удаления
	function _clear_cache( $only_stub = '' ){
		$cache_dir = self::$opt->cache_folder;

		if( ! $cache_dir ){
			self::show_message( __('Path to cache not set.','kama-thumbnail'), 'error');
			return false;
		}

		if( is_dir($cache_dir) ){
			if( $only_stub ){
				foreach( glob("$cache_dir/stub_*") as $file )
					unlink($file);

				if( WP_DEBUG && current_user_can('manage_options') )
					self::show_message( __('All nophoto thumbs was deleted from <b>Kama Thumbnail</b> cache.','kama-thumbnail'), 'notice-info' );
			}
			else {
				self::_clear_folder( $cache_dir );
				self::show_message( __('<b>Kama Thumbnail</b> cache has been cleared.','kama-thumbnail') );
			}
		}

		return true;
	}

	## код удаления метаполей
	function _delete_meta(){
		global $wpdb;

		if( ! self::$opt->meta_key )
			return self::show_message('meta_key option not set.', 'error');

		if( is_multisite() ){
			$deleted = '';
			foreach( function_exists('get_sites') ? get_sites() : wp_get_sites() as $site )
				$deleted .= $wpdb->delete( $wpdb->get_blog_prefix($site->blog_id) .'postmeta', array('meta_key' => self::$opt->meta_key ) ) ? '1' : '';
		}
		else
			$deleted = $wpdb->delete( $wpdb->postmeta, array('meta_key' => self::$opt->meta_key ) );

		if( $deleted )
			self::show_message( sprintf( __('All custom fields <code>%s</code> was deleted.','kama-thumbnail'), self::$opt->meta_key ) );
		else
			self::show_message( sprintf( __('Couldn\'t delete <code>%s</code> custom fields','kama-thumbnail'), self::$opt->meta_key ) );
	}

	/**
	 * Удаляет все файлы и папки в указанной директории.
	 * @param string $folder_path Путь до папки которую нужно очистить.
	 */
	static function _clear_folder( $folder_path, $del_current = false ){
		$folder_path = untrailingslashit( $folder_path );

		foreach( glob("$folder_path/*") as $file ){
			if( is_dir($file) ) call_user_func( __METHOD__, $file, true );
			else                unlink( $file );
		}

		if( $del_current ) rmdir( $folder_path );
	}

}

