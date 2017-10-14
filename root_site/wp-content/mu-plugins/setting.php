<?php

// ------------ Доп. записи ------------ //

// вывод общего сообщения об ошибки при не правильном вводе логина или пароля
function true_change_default_login_errors(){
	return '<strong>ОШИБКА</strong>: Вы ошиблись при вводе логина или пароля.';
}


show_admin_bar(false);

// Удаление не нужных скриптов и стилей из head
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');

// корректный вывод склонений к датам
function true_russian_date_forms($the_date = '') {
	if ( substr_count($the_date , '---') > 0 ) {
		return str_replace('---', '', $the_date);
	}
	// массив замен для русской локализации движка и для английской
	$replacements = array(
		"Январь" => "января", // "Jan" => "января"
		"Февраль" => "февраля", // "Feb" => "февраля"
		"Март" => "марта", // "Mar" => "марта"
		"Апрель" => "апреля", // "Apr" => "апреля"
		"Май" => "мая", // "May" => "мая"
		"Июнь" => "июня", // "Jun" => "июня"
		"Июль" => "июля", // "Jul" => "июля"
		"Август" => "августа", // "Aug" => "августа"
		"Сентябрь" => "сентября", // "Sep" => "сентября"
		"Октябрь" => "октября", // "Oct" => "октября"
		"Ноябрь" => "ноября", // "Nov" => "ноября"
		"Декабрь" => "декабря" // "Dec" => "декабря"
	);
	return strtr($the_date, $replacements);
}

// удаление версии wp из кода и рсс
function true_remove_wp_version_wp_head_feed() {
	return '';
}
add_filter('the_generator', 'true_remove_wp_version_wp_head_feed');

// добавление в футер админки свой текст
function true_change_admin_footer () {
	$footer_text = array(
		'Сайт разработал <a href="https://artemkomarov.ru" target="_blank">Артем Комаров</a>'
	);
	return implode( ' &bull; ', $footer_text);
}
add_filter('admin_footer_text', 'true_change_admin_footer');

/*
перенос css в футер 
function prefix_add_footer_styles() {
    wp_enqueue_style( 'maincss', get_template_directory_uri() . '/css/main.min.css', array(), '0.1', 'all' );
};
add_action( 'get_footer', 'prefix_add_footer_styles' );*/


/* Переопределяем дэфолтный jquery */
function add_scipts() {
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', get_template_directory_uri() . '/libs/jquery/dist/jquery.min.js', array(), '2.2.4', true);
		wp_enqueue_script('jquery');
	}
}
add_action('init', 'add_scipts');

/* Добавления страницы настроек для acf */
if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Настройка темы',
		'menu_title'	=> 'Настройка темы',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
}

/* Запрет на обновление плагинов */
/* Прописать в wp-config.php:
	$DISABLE_UPDATE = array( 'Название плагина');
 */

/*
function filter_plugin_updates( $update ) {    
    global $DISABLE_UPDATE; // см. wp-config.php
    if( !is_array($DISABLE_UPDATE) || count($DISABLE_UPDATE) == 0 ){  return $update;  }
    foreach( $update->response as $name => $val ){
        foreach( $DISABLE_UPDATE as $plugin ){
            if( stripos($name,$plugin) !== false ){
                unset( $update->response[ $name ] );
            }
        }
    }
    return $update;
}
add_filter( 'site_transient_update_plugins', 'filter_plugin_updates' );
*/


/* Обрезка текста (excerpt). Шоткоды вырезаются. Минимальное значение maxchar может быть 22. */
/* Вывод на странице: <?php echo kama_excerpt( array('maxchar'=>250) );	?> */

 function kama_excerpt( $args = '' ){
	global $post;

	$default = array(
		'maxchar'   => 350,   // количество символов.
		'text'      => '',    // какой текст обрезать (по умолчанию post_excerpt, если нет post_content.
							  // Если есть тег <!--more-->, то maxchar игнорируется и берется все до <!--more--> вместе с HTML
		'autop'     => true,  // Заменить переносы строк на <p> и <br> или нет
		'save_tags' => '',    // Теги, которые нужно оставить в тексте, например '<strong><b><a>'
		'more_text' => 'Читать дальше...', // текст ссылки читать дальше
	);

	if( is_array($args) ) $_args = $args;
	else                  parse_str( $args, $_args );

	$rg = (object) array_merge( $default, $_args );
	if( ! $rg->text ) $rg->text = $post->post_excerpt ?: $post->post_content;
	$rg = apply_filters('kama_excerpt_args', $rg );

	$text = $rg->text;
	$text = preg_replace ('~\[/?.*?\](?!\()~', '', $text ); // убираем шоткоды, например:[singlepic id=3], markdown +
	$text = trim( $text );

	// <!--more-->
	if( strpos( $text, '<!--more-->') ){
		preg_match('/(.*)<!--more-->/s', $text, $mm );

		$text = trim($mm[1]);

		$text_append = ' <a href="'. get_permalink( $post->ID ) .'#more-'. $post->ID .'">'. $rg->more_text .'</a>';
	}
	// text, excerpt, content
	else {
		$text = trim( strip_tags($text, $rg->save_tags) );

		// Обрезаем
		if( mb_strlen($text) > $rg->maxchar ){
			$text = mb_substr( $text, 0, $rg->maxchar );
			$text = preg_replace('~(.*)\s[^\s]*$~s', '\\1 ...', $text ); // убираем последнее слово, оно 99% неполное
		}
	}

	// Сохраняем переносы строк. Упрощенный аналог wpautop()
	if( $rg->autop ){
		$text = preg_replace(
			array("~\r~", "~\n{2,}~", "~\n~",   '~</p><br ?/>~'),
			array('',     '</p><p>',  '<br />', '</p>'),
			$text
		);
	}

	$text = apply_filters('kama_excerpt', $text, $rg );

	if( isset($text_append) ) $text .= $text_append;

	return ($rg->autop && $text) ? "<p>$text</p>" : $text;
}


/* Постраничная пагинация */
/* Вывод на странице: <?php	wpbeginner_numeric_posts_nav(); ?> */

function wpbeginner_numeric_posts_nav() {
	
		 if( is_singular() )
				 return;
	
		 global $wp_query;
	
		 /** Stop execution if there's only 1 page */
		 if( $wp_query->max_num_pages <= 1 )
				 return;
	
		 $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
		 $max   = intval( $wp_query->max_num_pages );
	
		 /** Add current page to the array */
		 if ( $paged >= 1 )
				 $links[] = $paged;
	
		 /** Add the pages around the current page to the array */
		 if ( $paged >= 3 ) {
				 $links[] = $paged - 1;
				 $links[] = $paged - 2;
		 }
	
		 if ( ( $paged + 2 ) <= $max ) {
				 $links[] = $paged + 2;
				 $links[] = $paged + 1;
		 }
	
		 echo '<div class="navigation"><ul>' . "\n";
	
		 /** Previous Post Link */
		 if ( get_previous_posts_link() )
				 printf( '<li>%s</li>' . "\n", get_previous_posts_link() );
	
		 /** Link to first page, plus ellipses if necessary */
		 if ( ! in_array( 1, $links ) ) {
				 $class = 1 == $paged ? ' class="active"' : '';
	
				 printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );
	
				 if ( ! in_array( 2, $links ) )
						 echo '<li>…</li>';
		 }
	
		 /** Link to current page, plus 2 pages in either direction if necessary */
		 sort( $links );
		 foreach ( (array) $links as $link ) {
				 $class = $paged == $link ? ' class="active"' : '';
				 printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
		 }
	
		 /** Link to last page, plus ellipses if necessary */
		 if ( ! in_array( $max, $links ) ) {
				 if ( ! in_array( $max - 1, $links ) )
						 echo '<li>…</li>' . "\n";
	
				 $class = $paged == $max ? ' class="active"' : '';
				 printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
		 }
	
		 /** Next Post Link */
		 if ( get_next_posts_link() )
				 printf( '<li>%s</li>' . "\n", get_next_posts_link() );
	
		 echo '</ul></div>' . "\n";
	
 }

 /* Замена лого при входе в админку */

function my_custom_login_logo(){
	echo '<style type="text/css">
	h1 a { background-image:url('.get_bloginfo('template_directory').'/img/login.jpg) !important; }
	</style>';
}
add_action('login_head', 'my_custom_login_logo');

/* Меняем картинку логотипа WP в админке */
function my_admin_logo() {
   echo '<style type="text/css">#header-logo { background:url('.get_bloginfo('template_directory').'/img/favicon/favicon.png) no-repeat 0 0 !important; }</style>';
}
add_action('admin_head', 'my_admin_logo');
 
/* Меняем картинку логотипа WP на странице входа */
function my_login_logo(){
   echo '<style type="text/css">#login h1 a { background: url('. get_bloginfo('template_directory') .'/img/login.png) no-repeat 0 0 !important; width: 228px; hight: 82px; }</style>';
}
add_action('login_head', 'my_login_logo');
/* Ставим ссыллку с логотипа на сайт, а не на wordpress.org */
add_filter( 'login_headerurl', create_function('', 'return get_home_url();') );
/* убираем title в логотипе "сайт работает на wordpress" */
add_filter( 'login_headertitle', create_function('', 'return false;') );