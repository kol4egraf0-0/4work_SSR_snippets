# Добавляет SVG в список разрешенных для загрузки файлов.
add_filter( 'upload_mimes', 'svg_upload_allow' );
function svg_upload_allow( $mimes ) {
	$mimes['svg']  = 'image/svg+xml';

	return $mimes;
}

# Исправление MIME типа для SVG файлов.
# SAFE SVG PR1ME
add_filter( 'wp_check_filetype_and_ext', 'fix_svg_mime_type', 10, 5 );
function fix_svg_mime_type( $data, $file, $filename, $mimes, $real_mime = '' ){

	// WP 5.1 +
	if( version_compare( $GLOBALS['wp_version'], '5.1.0', '>=' ) ){
		$dosvg = in_array( $real_mime, [ 'image/svg', 'image/svg+xml' ] );
	}
	else {
		$dosvg = ( '.svg' === strtolower( substr( $filename, -4 ) ) );
	}

	// mime тип был обнулен, поправим его
	// а также проверим право пользователя
	if( $dosvg ){

		// разрешим
		if( current_user_can('manage_options') ){

			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		}
		// запретим
		else {
			$data['ext']  = false;
			$data['type'] = false;
		}

	}

	return $data;
}

// 1. Полностью отключаем функционал комментариев
add_action('init', 'disable_comments');
function disable_comments() {
    // Удаление поддержки комментариев
    remove_post_type_support('post', 'comments');
    remove_post_type_support('page', 'comments');
    // Отключение комментариев
    update_option('default_comment_status', 'closed');
}

// 2. Редиректим в админке со всех технических страниц комментариев на главную админку
add_action('admin_init', 'redirect_comments_to_dashboard');
function redirect_comments_to_dashboard() {
    global $pagenow;
    if (( $pagenow === 'edit-comments.php' ) && ( !isset($_GET['comment_type']) || $_GET['comment_type'] === 'comment' )) {
        wp_redirect(admin_url());
        exit;
    }
}

// 3. Удаление пункта меню "Комментарии" из админки
add_action('admin_menu', 'remove_comments_menu');
function remove_comments_menu() {
    remove_menu_page('edit-comments.php');
}

// Отключение возможности создания сети сайтов в WordPress
define('WP_ALLOW_MULTISITE', false);

// 1. Отключение обновлений WordPress и уведомлений об этом
add_filter('pre_site_transient_update_core', '__return_null');
add_filter('pre_site_transient_update_plugins', '__return_null');
add_filter('pre_site_transient_update_themes', '__return_null');
add_filter('pre_option_update_core', '__return_null');
add_filter('pre_option_update_plugins', '__return_null');
add_filter('pre_option_update_themes', '__return_null');
remove_action('load-update-core.php', 'wp_update_plugins');
add_filter('pre_site_transient_update_core', create_function('$a', "return null;")); // На всякий случай для старых версий PHP

// 2. Отключение обновлений тем WordPress и уведомлений об этом
remove_action('load-update-core.php', 'wp_update_themes');
add_filter('pre_site_transient_update_themes', create_function('$a', "return null;")); // На всякий случай для старых версий PHP

// 3. Отключение обновлений плагинов WordPress и уведомлений об этом
add_filter('site_transient_update_plugins', '__return_false');
remove_action('load-update-core.php', 'wp_update_plugins');

// Отключение комментария Yoast SEO
add_action('wp_head', function() {
    ob_start(function($output) {
        return preg_replace('/<!-- This site is optimized with the Yoast SEO plugin.*?-->/ms', '', $output);
    });
}, 1);

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.bundle.min.js');
	wp_enqueue_script('slick', get_template_directory_uri() . '/assets/js/slick.min.js');
	wp_enqueue_script('fancybox', get_template_directory_uri() . '/assets/js/fancybox.js');
	wp_enqueue_script('custom', get_template_directory_uri() . '/assets/js/custom.js');
	// wp_enqueue_script('custom2', get_template_directory_uri() . '/assets/js/custom2.js');
});