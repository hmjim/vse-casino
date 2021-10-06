<?php

/**
 * Класс для создания отдельной миниатюры и функции обертки для этого класса.
 */


/**
 * Вернет только ссылку на миниатюру.
 *
 * @param array  $args
 * @param string $src
 *
 * @return string
 */
function kama_thumb_src( $args = [], $src = 'notset' ){
	$kt = new Kama_Make_Thumb( $args, $src );
	return $kt->src();
}

/**
 * Вернет картинку миниатюры (готовый тег img).
 *
 * @param array  $args
 * @param string $src
 *
 * @return string
 */
function kama_thumb_img( $args = [], $src = 'notset' ){
	$kt = new Kama_Make_Thumb( $args, $src );
	return $kt->img();
}

/**
 * Вернет картинку миниатюры, которая будет анкором ссылки на оригинал.
 *
 * @param array  $args
 * @param string $src
 *
 * @return mixed|string|void
 */
function kama_thumb_a_img( $args = [], $src = 'notset' ){
	$kt = new Kama_Make_Thumb( $args, $src );
	return $kt->a_img();
}

/**
 * Обращение к последнему экземпляру за свойствами класса: высота, ширина или др...
 *
 * @param string $optname
 *
 * @return Kama_Make_Thumb
 */
function kama_thumb( $optname = '' ){

	$instance = Kama_Make_Thumb::$last_instance;

	if( ! $optname )
		return $instance;

	if( property_exists( $instance, $optname ) )
		return $instance->$optname;
}


class Kama_Make_Thumb {

	public $debug = null; // устанавливается в настройках админки

	public $src;      // str
	public $width;    // int
	public $height;   // int
	public $crop;     // bool/array
	public $quality;  // int/float
	public $post_id;  // int
	public $no_stub;  // bool
	public $stub_url; // string

	public $notcrop;    // в приоритете над crop
	public $rise_small; // не увеличивать маленькие картинки до указанных размеров. С версии 3.6.

	public $args;  // переданные аргументы
	public $opt;   // опции плагина

	protected $thumb_path;
	protected $thumb_url;

	static $last_instance; // последний экземпляр, чтобы был доступ к $width, $height и другим данным...

	function __construct( $args = array(), $src = 'notset' ){

		$this->opt = clone Kama_Thumbnail::$opt;

		// добавляем разрешенные
		$this->opt->allow_hosts = array_merge( $this->opt->allow_hosts, array(
			self::parse_main_dom( $_SERVER['HTTP_HOST'] ),
			'youtube.com', 'youtu.be'
		) );

		if( $this->debug === null )
			$this->debug = !empty( $this->opt->debug );

		$this->set_args( $args, $src );

		self::$last_instance = $this;
	}

	/**
	 * Получает ссылку на картинку из произвольного поля текущего поста.
	 * Или из текста и создает произвольное поле.
	 * Если в тексте картинка не нашлась, то в произвольное поле запишется заглушка `no_photo`.
	 *
	 * @return string
	 */
	function get_src_and_set_postmeta(){
		global $post, $wpdb;

		$post_id = intval( $this->post_id ?: $post->ID );

		if( $src = get_post_meta( $post_id, $this->opt->meta_key, true ) )
			return $src;

		// проверяем наличие стандартной миниатюры
		if( $_thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true ) )
			$src = wp_get_attachment_url( (int) $_thumbnail_id );

		// получаем ссылку из контента
		if( ! $src ){
			$content = $this->post_id ? $wpdb->get_var("SELECT post_content FROM $wpdb->posts WHERE ID = ". intval($this->post_id) ." LIMIT 1") : $post->post_content;
			$src = $this->_get_url_from_text( $content );
		}

		// получаем ссылку из вложений - первая картинка
		if( ! $src ){
			$attch_img = get_children( [
				'numberposts'    => 1,
				'post_mime_type' => 'image',
				'post_parent'    => $post_id,
				'post_type'      => 'attachment'
			] );

			if( $attch_img = array_shift( $attch_img ) )
				$src = wp_get_attachment_url( $attch_img->ID );
		}

		// Заглушка no_photo, чтобы постоянно не проверять
		if( ! $src )
			$src = 'no_photo';

		update_post_meta( $post_id, $this->opt->meta_key, wp_slash($src) );

		return $src;
	}

	/**
	 * Ищет ссылку на картинку в тексте и возвращает её.
	 *
	 * @param string $text
	 *
	 * @return mixed|string|void
	 */
	function _get_url_from_text( $text ){

		$allows_patt = '';
		if( ! in_array('any', $this->opt->allow_hosts ) ){
			$hosts_regex = implode( '|', array_map('preg_quote', $this->opt->allow_hosts ) );
			$allows_patt = '(?:www\.)?(?:'. $hosts_regex .')';
		}

		$hosts_patt = '(?:https?://'. $allows_patt .'|/)';

		if(
			( false !== strpos( $text, 'src=') ) &&
			preg_match('~(?:<a[^>]+href=[\'"]([^>]+)[\'"][^>]*>)?<img[^>]+src=[\'"]\s*('. $hosts_patt .'.*?)[\'"]~i', $text, $match )
		){
			// проверяем УРЛ ссылки
			$src = $match[1];
			if( ! preg_match('~\.(jpg|jpeg|png|gif)(?:\?.+)?$~i', $src) || ! $this->_is_allow_host($src) ){
				// проверям УРЛ картинки, если не подходит УРЛ ссылки
				$src = $match[2];
				if( ! $this->_is_allow_host($src) )
					$src = '';
			}

			return $src;
		}

		return apply_filters( 'kama_thumb__get_url_from_text', '', $text );
	}

	/**
	 * Проверяет что картинка с разрешенного хоста.
	 *
	 * @param string $url
	 *
	 * @return bool|mixed|void
	 */
	function _is_allow_host( $url ){

		// pre filter to change the behavior
		if( $return = apply_filters( 'kama_thumb_is_allow_host', false, $url, $this->opt ) )
			return $return;

		if(
			( $url{0} === '/' && $url{1} !== '/' ) || // relative url
			in_array('any', $this->opt->allow_hosts )
		)
			return true;

		$host = self::parse_main_dom( parse_url($url, PHP_URL_HOST) );
		if( $host && in_array( $host, $this->opt->allow_hosts ) )
			return true;

		return false;
	}

	/**
	 * Get main domain name from maybe subdomain: foo.site.com > site.com | sub.site.co.uk > site.co.uk | sub.site.com.ua > site.com.ua
	 *
	 * @param  string $host host like: site.ru, site1.site.ru, xn--n1ade.xn--p1ai
	 *
	 * @return string Main domain name
	 */
	static function parse_main_dom( $host ){
		// если передан URL
		if( false !== strpos($host, '/') )
			$host = parse_url( $host, PHP_URL_HOST );

		// для http://localhost/foo или IP
		if( ! strpos($host, '.') || filter_var($host, FILTER_VALIDATE_IP) )
			return $host;

		// for cirilic domains: .сайт, .онлайн, .дети, .ком, .орг, .рус, .укр, .москва, .испытание, .бг
		if( false !== strpos($host, 'xn--') )
			preg_match('~xn--[^.]+\.xn--[^.]+$~', $host, $mm );
		// foo.academy
		else
			preg_match('~[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,10}$~', $host, $mm );

		return $mm[0];
	}

	/**
	 * Устанавливает свойства класса width или height, если они неизвестны или не точные (при notcrop).
	 * Данные могут пригодится для добавления в HTML...
	 */
	protected function _checkset_width_height(){

		if( $this->width && $this->height && $this->crop )
			return;

		list( $width, $height, $type, $attr ) = getimagesize( $this->thumb_path ); // скорость работы - 2 сек на 50к запросов - быстро

		// не кадрируется и поэтому одна из сторон всегда будет отличаться от указанной...
		if( ! $this->crop ){
			if( $width )  $this->width = $width;
			if( $height ) $this->height = $height;
		}
		// кадрируется, но одна из сторон может быть не указана, проверим и определим если надо
		else {
			if( ! $this->width )  $this->width  = $width;
			if( ! $this->height ) $this->height = $height;
		}
	}

	/**
	 * Create thumbnail.
	 *
	 * @return false|string  Thumbnail URL or false.
	 */
	function do_thumbnail(){

		// если не передана ссылка, то ищем её в контенте и записываем пр.поле
		if( ! $this->src )
			$this->src = $this->get_src_and_set_postmeta();

		if( ! $this->src ){
			trigger_error( 'ERROR: No $src prop.', E_USER_NOTICE );
			return false;
		}

		// нужна ли картинка-заглушка
		if( 'no_photo' === $this->src ){
			if( $this->no_stub )
				return false;
			else
				$this->src = $this->stub_url;
		}

		// fix URL
		//$this->src = urldecode( $this->src );          // не обязательно дальше сам декодит...
		$this->src = html_entity_decode( $this->src ); // 'sd&#96;asd.jpg' to 'sd`asd.jpg'

		$srcpath = parse_url( $this->src, PHP_URL_PATH );

		// картинка не определена
		if( ! $srcpath )
			return false;

		// позволяет обработать src и вернуть его прервав дальнейшее выполенение кода.
		if( $_src = apply_filters_ref_array( 'pre_do_thumbnail_src', [ '', & $this ] ) )
			return $_src;

		// пропускаем SVG
		if( 'svg' === strtolower( substr($srcpath, -3) ) )
			return $this->src;

		preg_match( '~\.([a-z0-9]{2,4})$~i', $srcpath, $m );
		$ext = ! empty($m[1]) ? $m[1] : 'png';

		$_suffix = '';
		if( ! $this->crop && ( $this->height && $this->width ) )
			$_suffix .= '_notcrop';
		if( is_array($this->crop) && preg_match('~top|bottom|left|right~', implode('/', $this->crop), $mm) )
			$_suffix .= "_$mm[0]";
		if( ! $this->rise_small )
			$_suffix .= '_notrise';

		// TODO определять ссылку не по file_exists а по реальному контенту файла.
		// $str = file_get_contents( $file, false, null, 100, 200 );
		// на скорости при этом почти не теряем: file_exists: 0,2 сек и file_get_contents: 0,3 сек на 50к итераций

		// OLD - $file_name = substr( md5($srcpath), -9 ) . "_{$this->width}x{$this->height}$_suffix.$ext";
		$srcpath_md5      = md5( $srcpath );
		$file_name        = substr( $srcpath_md5, -15 ) . "_{$this->width}x{$this->height}$_suffix.$ext";
		$sub_dir          = substr( $srcpath_md5, -2 );
		$cache_folder     = $this->opt->cache_folder     . "/$sub_dir";
		$cache_folder_url = $this->opt->cache_folder_url . "/$sub_dir";
		$this->thumb_path = $cache_folder     . "/$file_name";
		$this->thumb_url  = $cache_folder_url . "/$file_name";

		$thumb_url = ''; // готовый URL картинки

		// CACHE - если миниатюра уже есть, то возвращаем её
		if( ! $this->debug ){

			$thumb_url = apply_filters_ref_array( 'cached_thumb_url', [ '', & $this ] );

			if( ! $thumb_url && file_exists($this->thumb_path) )
				$thumb_url = $this->thumb_url;

			// если есть заглушка возвращаем её
			if( ! $thumb_url && file_exists( $stub_thumb_path = $this->add_stub_to_path($this->thumb_path) ) ){
				$this->thumb_path = $stub_thumb_path;
				$this->thumb_url  = $this->add_stub_to_path( $this->thumb_url );

				if( $this->no_stub )
					return false;

				$thumb_url = $this->thumb_url;
			}

			// Кэш найден. Установим/проверим оригинальные размеры...
			if( $thumb_url ){
				$this->_checkset_width_height();

				return $thumb_url;
			}
		}


		// NOT CACHE -

		$is_no_photo = false;

		if( ! self::_check_create_folder($cache_folder) ){
			if( class_exists('Kama_Thumbnail') ){
				Kama_Thumbnail::show_message(
					sprintf( __('Folder where thumbs will be created not exists. Create it manually: "s%"','kama-thumbnail'), $this->opt->cache_folder ), 'error'
				);
				return false;
			}
			else
				die( 'Kama_Thumbnail: No cache folder. Create it: '. $this->opt->cache_folder );
		}

		if( ! $this->_is_allow_host($this->src) ){
			$this->src   = $this->stub_url;
			$is_no_photo = true;
		}

		$this->src = self::_fix_src_start( $this->src );

		// Если не удалось получить картинку: недоступный хост, файл пропал после переезда или еще чего.
		// То для указаного УРЛ будет создана миниатюра из заглушки no_photo.jpg
		// Чтобы после появления файла, миниатюра создалась правильно, нужно очистить кэш плагина.
		$img_str = $this->get_img_string( $this->src );
		$size = $img_str ? self::_image_size_from_string( $img_str ) : false;

		if( ! $size || empty($size['mime']) || false === strpos( $size['mime'], 'image') ){
			$is_no_photo = true;
			$this->src   = self::_fix_src_start( $this->stub_url );
			$img_str     = $this->get_img_string( $this->src );
		}

		// Изменим название файла если это картинка заглушка
		if( $is_no_photo ){
			$this->thumb_path = $this->add_stub_to_path( $this->thumb_path );
			$this->thumb_url  = $this->add_stub_to_path( $this->thumb_url );
		}

		if( ! $img_str ){
			trigger_error( 'ERROR: Couldn`t get img data, even no_photo.', E_USER_NOTICE );
			return false;
		}

		// создаем миниатюру
		// Imagick
		if( extension_loaded('imagick') ){

			if( $this->make_thumbnail_Imagick( $img_str ) )
				$thumb_url = $this->thumb_url;
		}
		// GD
		elseif( extension_loaded('gd') ){
			$this->make_thumbnail_GD( $img_str );

			$thumb_url = $this->thumb_url;
		}
		// нет библиотеки
		else
			trigger_error( 'ERROR: There is no one of the Image libraries (GD or Imagick) installed on your server.', E_USER_NOTICE );

		return $thumb_url;
	}

	/**
	 * Исправляет указанный URL: добавляет протокол, добавляет домен (для относительных ссылок) и т.д.
	 *
	 * @param string $src
	 *
	 * @return string
	 */
	static function _fix_src_start( $src ){

		// УРЛ без протокола: //site.ru/foo
		if( 0 === strpos($src, '//') )
			$src = ( is_ssl() ? 'https' : 'http' ) . ":$src";
		// относительный УРЛ
		elseif( '/' === $src{0} )
			$src = home_url( $src );

		return $src;
	}

	/**
	 * Добавляет в конец назыания файла 'stub'
	 * @param  string $path_url Путь до файла или URL файла.
	 * @return string Обработанную строку.
	 */
	function add_stub_to_path( $path_url ){
		$dpath = dirname( dirname( $path_url ) ); // удалим поддиректорию - /3d/
		$bname = basename( $path_url );
		return $dpath . "/stub_$bname";
	}

	/**
	 * Получает реальные размеры картинки.
	 *
	 * @param string $img_data
	 *
	 * @return array|bool
	 */
	static function _image_size_from_string( $img_data ){

		if( function_exists('getimagesizefromstring') )
			return getimagesizefromstring( $img_data );

		return getimagesize( 'data://application/octet-stream;base64,'. base64_encode($img_data) );
	}

	/**
	 * Проверяет наличие указанной директории, пытается создать, если её нет.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	static function _check_create_folder( $path ){
		$is = true;
		if( ! is_dir( $path ) )
			$is = mkdir( $path, 0755, true );

		return $is;
	}

	/**
	 * Пытается получить данные картинки (в виде строки) по указанному URL картинки.
	 *
	 * @param string $img_url
	 *
	 * @return string Данные картинки или пустую строку.
	 */
	function get_img_string( $img_url ){

		$img_str = '';

		if( false === strpos( $img_url, 'http') && '//' !== substr( $img_url, 0, 2 )  )
			die( 'ERROR: image url begins with not "http" or "//" — ' . esc_html($img_url) );

		// by ABSPATH ----
		//if(0) // off
		if( ! $img_str && strpos( $img_url, $_SERVER['HTTP_HOST'] ) ){
			// получим корень сайта $_SERVER['DOCUMENT_ROOT'] может быть неверный
			$root = ABSPATH;

			// maybe WP in sub dir?
			$root_parent = dirname( ABSPATH ) .'/';
			if( file_exists( $root_parent . 'wp-config.php') && ! file_exists( $root_parent . 'wp-settings.php' ) ){
				$root = $root_parent;
			}

			$img_path = preg_replace('~^https?://[^/]+/(.*)$~', "$root\\1", $img_url );
			if( file_exists( $img_path ) )
				$img_str = $this->debug ? file_get_contents( $img_path ) : @ file_get_contents( $img_path );
		}

		// WP HTTP API ----
		//if(0) // off
		if( ! $img_str && function_exists('wp_remote_get') ){
			$img_str = wp_remote_retrieve_body( wp_remote_get($img_url) );
		}

		// by URL ----
		//if(0) // off
		if( ! $img_str && ini_get('allow_url_fopen') ){

			// try find 200 OK. it may be 301, 302 redirects. In 3** redirect first status will be 3** and next 200 ...
			$OK_200 = false;
			$headers = (array) @ get_headers( $img_url );
			foreach( $headers as $line ){
				if( false !== strpos( $line, '200 OK' ) ){
					$OK_200 = true;
					break;
				}
			}

			if( $OK_200 )
				$img_str = file_get_contents( $img_url );
		}

		// CURL ----
		//if(0) // off
		if( ! $img_str && (extension_loaded('curl') || function_exists('curl_version')) ){

			$ch = curl_init();

			curl_setopt_array( $ch, [
				CURLOPT_URL            => $img_url,
				CURLOPT_FOLLOWLOCATION => true,  // To make cURL follow a redirect
				CURLOPT_HEADER         => false,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYPEER => false, // accept any server certificate
			]);

			$img_str = curl_exec( $ch );

			//$errmsg = curl_error( $ch );
			$info = curl_getinfo( $ch );

			curl_close( $ch );

			if( @ $info['http_code'] != 200 )
				$img_str = '';
		}

		// если по URL вернулся HTML код (например страница 404)
		if( false !== strpos( $img_str, '<!DOCTYPE') )
			$img_str = '';

		return $img_str;
	}

	/**
	 * Ядро: создание и запись файла-картинки на основе библиотеки Imagick
	 *
	 * @param string $img_string
	 *
	 * @return bool
	 */
	protected function make_thumbnail_Imagick( $img_string ){

		try {

			$dest  = $this->thumb_path;
			$image = new Imagick();

			$image->readImageBlob( $img_string );

			// Select the first frame to handle animated images properly
			if( is_callable( [ $image, 'setIteratorIndex' ] ) )
				$image->setIteratorIndex(0);

			// устанавливаем качество
			$format = $image->getImageFormat();
			if( in_array( $format, ['JPEG', 'JPG'] ) )
				$image->setImageCompression( Imagick::COMPRESSION_JPEG );

			$image->setImageCompressionQuality( $this->quality );

			$origin_h = $image->getImageHeight();
			$origin_w = $image->getImageWidth();

			// получим координаты для считывания с оригинала и размер новой картинки
			list( $dx, $dy, $wsrc, $hsrc, $width, $height ) = $this->_resize_coordinates( $origin_w, $origin_h );

			// обрезаем оригинал
			$image->cropImage( $wsrc, $hsrc, $dx, $dy );
			$image->setImagePage( $wsrc, $hsrc, 0, 0 );

			// strip out unneeded meta data
			$image->stripImage();

			// уменьшаем под размер
			$image->scaleImage( $width, $height );

			$image->writeImage( $dest );
			chmod( $dest, 0644 );
			$image->clear();
			$image->destroy();

			// установим/изменим размеры картинки в свойствах класса, если надо
			$this->_checkset_width_height();

			return true;
		}
		catch( ImagickException $e ){
			trigger_error( 'ERROR: ImagickException message: '. $e->getMessage(), E_USER_NOTICE );
			return false;
		}

	}

	/**
	 * Ядро: создание и запись файла-картинки на основе библиотеки GD
	 *
	 * @param string $img_string
	 *
	 * @return bool
	 */
	protected function make_thumbnail_GD( $img_string ){

		$dest = $this->thumb_path;
		$size = self::_image_size_from_string( $img_string );

		if( $size === false )
			return false; // не удалось получить параметры файла;

		list( $origin_w, $origin_h ) = $size;

		$format = strtolower( substr( $size['mime'], strpos($size['mime'], '/') + 1 ) );

		// Создаем ресурс картинки
		$image = imagecreatefromstring( $img_string );
		if ( ! is_resource( $image ) )
			return false; // не получилось получить картинку

		// получим координаты для считывания с оригинала и размер новой картинки
		list( $dx, $dy, $wsrc, $hsrc, $width, $height ) = $this->_resize_coordinates( $origin_w, $origin_h );

		// Создаем холст полноцветного изображения
		$thumb = imagecreatetruecolor( $width, $height );

		if( function_exists('imagealphablending') && function_exists('imagesavealpha') ) {
			imagealphablending( $thumb, false ); // режим сопряжения цвета и альфа цвета
			imagesavealpha( $thumb, true ); // флаг сохраняющий прозрачный канал
		}
		if( function_exists('imageantialias') )
			imageantialias( $thumb, true ); // включим функцию сглаживания

		if( ! imagecopyresampled( $thumb, $image, 0, 0, $dx, $dy, $width, $height, $wsrc, $hsrc ) )
			return false; // не удалось изменить размер

		//
		// Сохраняем картинку
		if( 'png' === $format ){
			// convert from full colors to index colors, like original PNG.
			if ( function_exists('imageistruecolor') && ! imageistruecolor( $thumb ) ){
				imagetruecolortopalette( $thumb, false, imagecolorstotal( $thumb ) );
			}

			imagepng( $thumb, $dest );
		}
		elseif( 'gif' === $format ){
			imagegif( $thumb, $dest );
		}
		else {
			imagejpeg( $thumb, $dest, $this->quality );
		}
		chmod( $dest, 0644 );
		imagedestroy( $image );
		imagedestroy( $thumb );

		// установим/изменим размеры картинки в свойствах класса, если надо
		$this->_checkset_width_height();

		return true;
	}

	/**
	 * Получает координаты кадрирования.
	 *
	 * @param int $origin_w Оригинальная ширина
	 * @param int $origin_h Оригинальная высота
	 *
	 * @return array   отступ по Х и Y и сколько пикселей считывать по высоте и ширине у источника: $dx, $dy, $wsrc, $hsrc
	 */
	protected function _resize_coordinates( $origin_w, $origin_h ){

		// если указано не увеличивать картинку и она меньше указанных размеров, укажем максимальный размер - это размер самой картинки
		// важно указать глобальные значения, они юзаются в width и height IMG атрибута и может еще где-то...
		if( ! $this->rise_small ){
			if( $origin_w < $this->width )  $this->width  = $origin_w;
			if( $origin_h < $this->height ) $this->height = $origin_h;
		}

		$crop   = $this->crop;
		$width  = $this->width;
		$height = $this->height;

		// елси не нужно кадрировать и указаны обе стороны, то находим меньшую подходящую сторону у картинки и обнуляем её
		if( ! $crop && ($width > 0 && $height > 0) ){
			if( $width/$origin_w < $height/$origin_h )
				$height = 0;
			else
				$width = 0;
		}

		// если не указана одна из сторон задаем ей пропорциональное значение
		if( ! $width ) 	$width  = round( $origin_w * ($height/$origin_h) );
		if( ! $height ) $height = round( $origin_h * ($width/$origin_w) );

		// определяем необходимость преобразования размера так чтоб вписывалась наименьшая сторона
		// if( $width < $origin_w || $height < $origin_h )
			$ratio = max( $width/$origin_w, $height/$origin_h );

		// определяем позицию кадрирования
		$dx = $dy = 0;
		if( is_array($crop) ){

			$xx = $crop[0];
			$yy = $crop[1];

			// срезать слева и справа
			if( $height/$origin_h > $width/$origin_w ){
				if(0){}
				elseif( $xx === 'center' ) $dx = round( ($origin_w - $width * ($origin_h/$height)) / 2 ); // отступ слева у источника
				elseif( $xx === 'left' )   $dx = 0;
				elseif( $xx === 'right' )  $dx = round( ($origin_w - $width * ($origin_h/$height)) ); // отступ слева у источника
			}
			// срезать верх и низ
			else {
				if(0){}
				elseif( $yy === 'center' ) $dy = round( ($origin_h - $height * ($origin_w/$width)) / 2 );
				elseif( $yy === 'top' )    $dy = 0;
				elseif( $yy === 'bottom' ) $dy = round( ($origin_h - $height * ($origin_w/$width)) );
				// $height*$origin_w/$width)/2*6/10 - отступ сверху у источника *6/10 - чтобы для вертикальных фоток отступ сверху был не половина а процентов 30
			}
		}

		// сколько пикселей считывать c источника
		$wsrc = round( $width/$ratio );
		$hsrc = round( $height/$ratio );

		return array( $dx, $dy, $wsrc, $hsrc, $width, $height );
	}


	// МИНИАТЮРЫ ---

	/**
	 * Обработка параметров для создания миниатюр.
	 *
	 * @param array  $args
	 * @param string $src
	 */
	protected function set_args( $args = [], $src = 'notset' ){

		// все параметры без алиасов
		$def = apply_filters( 'kama_thumb_default_args', [
			//'notcrop'   => '',  // detects by isset()
			//'no_stub'   => '',  // detects by isset()
			//'yes_stub'  => '',  // detects by isset()
			'stub_url'    => $this->opt->no_photo_url,  // url картинки заглушки
			'allow'       => '',  // разрешенные хосты для этого запроса, чтобы не указывать настройку глобально
			'width'       => '',  // пропорционально
			'height'      => '',  // пропорционально
			'attach_id'   => is_numeric($src) ? intval($src) : 0,
						   // ID изображения (вложения) в структуре WordPress.
						   // Этот ID можно еще указать числом в параметре src или во втором параметре функции kama_thumb_*()
			'src'         => $src, // алиасы 'url', 'link', 'img'
			'quality'     => $this->opt->quality,
			'post_id'     => '', // алиас 'post'

			'rise_small'  => $this->opt->rise_small, // увеличивать ли изображения, если они меньше указанных размеров. По умолчанию: true.
			'crop'        => true, // чтобы отключить кадрирование, укажите: 'false/0/no/none' или определите параметр 'notcrop'.
								   // можно указать строку: 'right/bottom' или 'top', 'bottom', 'left', 'right', 'center' и любые их комбинации.
								   // это укажет область кадрирования:
								   // 'left', 'right' - для горизонтали
								   // 'top', 'bottom' - для вертикали
								   // 'center' - для обоих сторон
								   // когда указывается одно значение, второе будет по умолчанию. По умолчанию 'center/center'
			// атрибуты тегов IMG и A
			'class'     => 'aligncenter',
			'style'     => '',
			'alt'       => '',
			'title'     => '',
			'attr'      => '', // произвольная строка, вставляется как есть

			'a_class'   => '',
			'a_style'   => '',
			'a_attr'    => '',
		] );

		if( is_string( $args ) ){
			// parse_str превращает пробелы в "_", например тут "w=230 &h=250 &notcrop &class=aligncenter" notcrop будет notcrop_
			$args = preg_replace( '~ +&~', '&', trim($args) );
			parse_str( $args, $rg );
		}
		else
			$rg = $args;

		$rg = array_merge( $def, $rg );

		foreach( $rg as & $val ){
			if( is_string($val) ) $val = trim( $val );
		}
		unset( $val );

		// алиасы параметров
		if( isset($rg['w']) )           $rg['width']   = $rg['w'];
		if( isset($rg['h']) )           $rg['height']  = $rg['h'];
		if( isset($rg['q']) )           $rg['quality'] = $rg['q'];
		if( isset($rg['post']) )        $rg['post_id'] = $rg['post'];
		if( is_object($rg['post_id']) ) $rg['post_id'] = $rg['post_id']->ID; // если в post_id передан объект поста
		if( isset($rg['url']) )         $rg['src']     = $rg['url'];
		elseif( isset($rg['link']) )    $rg['src']     = $rg['link'];
		elseif( isset($rg['img']) )     $rg['src']     = $rg['img'];

		if( $rg['attach_id'] && $atch_url = wp_get_attachment_url($rg['attach_id']) )
			$rg['src'] = $atch_url;
		if( in_array($rg['crop'], ['no','none'], true) )
			$rg['crop'] = false;

		// если src не указан - обнулим. Если указан и передано пустое значение - 'no_photo'
		// для случаев, когда в src указано: ''/null/false...
		if( 'notset' === $rg['src'] ) $rg['src'] = '';
		elseif( empty($rg['src']) )   $rg['src'] = 'no_photo';

		// установим свойства
		$this->src        = (string) $rg['src'];
		$this->stub_url   = (string) $rg['stub_url'];
		$this->width      = (int)    $rg['width'];
		$this->height     = (int)    $rg['height'];
		$this->quality    = (int)    $rg['quality'];
		$this->post_id    = (int)    $rg['post_id'];

		$this->notcrop    = isset($rg['notcrop']); // до $this->crop
		$this->crop       = $this->notcrop ? false : $rg['crop'];
		$this->rise_small = !! $rg['rise_small'];

		// размер миниатюр по умолчанию
		if( ! $this->width && ! $this->height ) $this->width = $this->height = 100;

		// кадрирование не имеет смысла если одна из сторон равна 0 - она всегда будет подограна пропорционально...
		if( ! $this->height || ! $this->width ) $this->crop = false;

		// превратим crop в массив, проверим параметры и дополним недостающие
		if( $this->crop ){

			if( in_array($this->crop, array(true, 1, '1'), true) ){
				$this->crop = array('center','center');
			}
			else {
				if( is_string($this->crop) )  $this->crop = preg_split('~[/,: -]~', $this->crop ); // top/right
				if( ! is_array($this->crop) ) $this->crop = array();

				$xx = & $this->crop[0];
				$yy = & $this->crop[1];

				// поправим если неправильно указаны оси...
				if( in_array($xx, array('top','bottom')) ){ $this->crop[1] = $xx; $this->crop[0] = 'center'; }
				if( in_array($yy, array('left','right')) ){ $this->crop[0] = $yy; $this->crop[1] = 'center'; }

				if( ! $xx || ! in_array($xx, array('left','center','right')) ) $xx = 'center';
				if( ! $yy || ! in_array($yy, array('top','center','bottom')) ) $yy = 'center';
			}
		}


		if( isset($rg['yes_stub']) )
			$this->no_stub = false;
		else
			$this->no_stub = ( isset($rg['no_stub']) || !empty($this->opt->no_stub) );

		// добавим разрешенные хосты
		if( $rg['allow'] ){
			foreach( preg_split('~[, ]+~', $rg['allow'] ) as $host )
				$this->opt->allow_hosts[] = ( $host === 'any' ) ? $host : self::parse_main_dom( $host );
		}

		$this->args = apply_filters( 'kama_thumb_set_args', $rg );
	}

	/**
	 * Создает миниатюру и/или получает URL миниатюры.
	 *
	 * @return string
	 */
	function src(){
		$src = $this->do_thumbnail();

		return apply_filters( 'kama_thumb_src', $src, $this->args );
	}

	/**
	 * Получает IMG тег миниатюры.
	 *
	 * @return string
	 */
	function img(){
		if( ! $src = $this->src() )
			return '';

		$rg = & $this->args;

		if( ! $rg['alt'] && $rg['attach_id'] )
			$rg['alt'] = get_post_meta( $rg['attach_id'], '_wp_attachment_image_alt', true );

		if( ! $rg['alt'] && $rg['title'] ) $rg['alt'] = $rg['title'];

		$attr = [];

		// width height на этот момент всегда точные!
		$attr[] = 'width="'. intval($this->width) .'" height="'. intval($this->height) .'"';

		$attr[] = 'alt="'. ($rg['alt'] ? esc_attr($rg['alt']) : '') .'"';

		if( $rg['title'] ) $attr[] = 'title="'. esc_attr( $rg['title'] ) .'"';
		if( $rg['class'] ) $attr[] = 'class="'. preg_replace('/[^A-Za-z0-9 _-]/', '', $rg['class'] ) .'"';
		if( $rg['style'] ) $attr[] = 'style="'. strip_tags($rg['style']) .'"';
		if( $rg['attr'] )  $attr[] = $rg['attr'];

		$out = '<img src="'. esc_url( $src ) .'" '. implode(' ', $attr) .'>';

		return apply_filters( 'kama_thumb_img', $out, $rg );
	}

	/**
	 * Получает IMG в A теге.
	 *
	 * @return string
	 */
	function a_img(){

		if( ! $img = $this->img() )
			return '';

		$rg = & $this->args;

		$attr  = array();
		if( $rg['a_class'] ) $attr[] = 'class="'. preg_replace('/[^A-Za-z0-9 _-]/', '', $rg['a_class'] ) .'"';
		if( $rg['a_style'] ) $attr[] = 'style="'. strip_tags($rg['a_style']) .'"';
		if( $rg['a_attr'] )  $attr[] = $rg['a_attr'];

		$out = '<a href="'. esc_url($this->src) .'"'.( $attr ? ' '.implode(' ', $attr) : '' ).'>'. $img .'</a>';

		return apply_filters( 'kama_thumb_a_img', $out, $rg );
	}

}

