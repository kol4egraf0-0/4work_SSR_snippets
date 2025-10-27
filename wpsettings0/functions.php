<?php
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

function safe_replace_htmltags_acf() {
  //безопасно чтоб ?run_fix=parol
    if (!is_user_logged_in()  !current_user_can('administrator')) {
        return;
    }
    if (!isset($_GET['run_fix'])  $_GET['run_fix'] !== 'parol') {
        return;
    }

  //id записей и др
    $post_ids = [2416...]; 

  //acf поле или основной контент название
    $field_name = ''; 
    echo '<pre>';

    foreach ($post_ids as $post_id) {


        $content = get_field($field_name, $post_id);
        if (!$content) {
            echo "запись не найдена: $post_id";
            continue;
        }

    //теги
        $tag_cont = substr_count($content, '<strong>') + substr_count($content, '</strong>');
        if ($tag_cont === 0) {
            echo "$post_id: теги не найдены.\n";
            continue;
        }

        $fixed = str_replace(['<strong>', '</strong>'], ['<b>', '</b>'], $content);
        update_field($field_name, $fixed, $post_id);

        echo "acf поле в записи обновлено $post_id: заменено $tag_cont столько то тегов\n";
    }

    echo '</pre>';
    exit;
}
add_action('admin_init', 'safe_replace_htmltags_acf');

function extend_search_to_all_fields($search, $query) {
    global $wpdb;

    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $search_term = $query->query_vars['s'];

        if (empty($search_term)) {
            return $search;
        }

        $excluded_field_types = [
            'true_false', // Тип "true/false"
            'checkbox',   // Тип "checkbox"
            'select',     // Тип "select"
            'radio',      // Тип "radio"
            'image',      // Тип "image"
            'file',       // Тип "file"
            'gallery',    // Тип "gallery"
            'relationship',// Тип "relationship"
            'taxonomy',   // Тип "taxonomy"
        ];

        $field_groups = acf_get_field_groups();

        $excluded_meta_keys = [];

        if ($field_groups) {
            foreach ($field_groups as $group) {
                $fields = acf_get_fields($group['ID']);

                if ($fields) {х
                    $fields = get_acf_fields_recursive($fields);

                    foreach ($fields as $field) {
                        if (in_array($field['type'], $excluded_field_types)) {
                            $excluded_meta_keys[] = $field['name'];
                        }
                    }
                }
            }
        }

        $search_sql = "
            AND (
                {$wpdb->posts}.post_title LIKE '%" . esc_sql($wpdb->esc_like($search_term)) . "%'
                OR {$wpdb->posts}.post_content LIKE '%" . esc_sql($wpdb->esc_like($search_term)) . "%'
                OR EXISTS (
                    SELECT * FROM {$wpdb->postmeta}
                    WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
                    AND {$wpdb->postmeta}.meta_value LIKE '%" . esc_sql($wpdb->esc_like($search_term)) . "%'
                    " . (!empty($excluded_meta_keys) ? "AND {$wpdb->postmeta}.meta_key NOT IN ('" . implode("','", array_map('esc_sql', $excluded_meta_keys)) . "')" : "") . "
                    AND {$wpdb->postmeta}.meta_value NOT LIKE 'a:%'
                )
            )
        ";

        return $search_sql;
    }

    return $search;
}
add_filter('posts_search', 'extend_search_to_all_fields', 10, 2);

// Рекурсивная функция для обхода всех полей, включая подполя
function get_acf_fields_recursive($fields) {
    $all_fields = [];
    foreach ($fields as $field) {
        if ($field['type'] === 'repeater' || $field['type'] === 'flexible_content') {
            if (!empty($field['sub_fields'])) {
                $all_fields = array_merge($all_fields, get_acf_fields_recursive($field['sub_fields']));
            }
        } else {
            $all_fields[] = $field;
        }
    }
    return $all_fields;
}

//js + css фалики ес чо свои подставить, наличие футера обязательно и page-main stat1c
function sp_scripts() {
    $template_uri = get_template_directory_uri();
    $theme_version = wp_get_theme()->get( 'Version' ); 

    $styles = [
        'bootstrap'       => '/assets/css/bootstrap.min.css',
        'animate'         => '/assets/css/animate.min.css',
        'magnific-popup'  => '/assets/css/magnific-popup.min.css',
        'nice-select'     => '/assets/css/nice-select.min.css',
        'slick'           => '/assets/css/slick.min.css',
        'style'           => '/assets/css/style.css',
        'fontawesome'     => '/assets/fonts/fontawesome/css/all.min.css',
    ];

    foreach ( $styles as $handle => $path ) {
        wp_enqueue_style( $handle, $template_uri . $path, array(), $theme_version ); 
    }

    wp_enqueue_style( 'cms-style', get_stylesheet_uri(), array(), $theme_version ); 

    wp_enqueue_script('main', $template_uri . '/assets/js/main.js', array(), $theme_version, true);

    wp_enqueue_script('wow', $template_uri . '/assets/js/wow.min.js', array('jquery'), $theme_version, true);
    wp_enqueue_script('slick', $template_uri . '/assets/js/slick.min.js', array('jquery'), $theme_version, true);
    wp_enqueue_script('magnific-popup', $template_uri . '/assets/js/magnific-popup.min.js', array('jquery'), $theme_version, true);
    wp_enqueue_script('jquery.nice-select', $template_uri . '/assets/js/jquery.nice-select.min.js', array('jquery'), $theme_version, true);
    wp_enqueue_script('jquery.inview', $template_uri . '/assets/js/jquery.inview.min.js', array('jquery'), $theme_version, true);
    wp_enqueue_script('isotope', $template_uri . '/assets/js/isotope.pkgd.min.js', array('jquery'), $theme_version, true);
    wp_enqueue_script('imagesloaded', $template_uri . '/assets/js/imagesloaded.pkgd.min.js', array('jquery'), $theme_version, true);
    wp_enqueue_script('bootstrap', $template_uri . '/assets/js/bootstrap.min.js', array('jquery'), $theme_version, true); 
}

//опции дефолтные для всех страниц нужные чот
function my_acf_op_init() {
	if( function_exists('acf_add_options_sub_page') ) {
		$option_page = acf_add_options_page(array(
            'page_title'    => __('Опции'),
            'menu_title'    => __('Опции'),
            'menu_slug'     => 'options',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    }
}

/**
 * MAXMA ползунок в корзине для выбора количества баллов в корзине.
 */
function maxma_add_bonuses_range() {
    if (!is_user_logged_in()) {
        return;
    }
    $bonuses = getClientBonuses();
    if ($exceptions = isExceptionsInCart()) {
        update_user_meta(get_current_user_id(), 'maxma_bonuses', 0);
        echo "<div class='maxma-bonuses'>";
        echo "<div>Всего бонусных баллов: {$bonuses}</div>";
        echo "<div>При применении скидки бонусы не списываются.</div>";
        echo "</div>";
        return;
    }

//  delete_user_meta(get_current_user_id(), 'maxma_bonuses');
    $bonusesCheck = calculatePurchaseMaxma();
    $maxToApply = !empty($bonusesCheck) ? $bonusesCheck['max_to_apply'] : 0;
    $bonusesToApply = $_POST['maxma_bonus'] ?? (get_user_meta(get_current_user_id(), 'maxma_bonuses', true) ?? ($maxToApply ?? 0));

    if (!empty($maxToApply)) {
        if (empty($bonusesToApply)) {
            $bonusesToApply = 0;
        }
        update_user_meta(get_current_user_id(), 'maxma_bonuses', wc_clean($bonusesToApply));
        echo "<div class='maxma-bonuses'>";
            echo "<div class='mb-text'>Оплатить бонусными баллами:</div>";
//            echo "<input class='maxma-range' name='maxma_bonus' type='number' value='{$bonusesToApply}' min='0' max='{$maxToApply}' oninput='this.nextElementSibling.value = this.value'>";
            echo "<input class='maxma-range' name='maxma_bonus' type='number' value='{$bonusesToApply}' min='0' max='{$maxToApply}'>";
//            echo "<input class='maxma-range' type='range' min='0' max='{$maxToApply}' value='{$bonusesToApply}' oninput='this.previousElementSibling.value = this.value'>";
              echo "<div>Доступно бонусных баллов к списанию: {$maxToApply}</div>";
              echo "<div>Всего бонусных баллов: {$bonuses}</div>";
//            echo "<div class='mb-outputs'>";
//                echo "<output class='mb-min'>0</output>";
//                echo "<output class='mb-max'>{$maxToApply}</output>";
//            echo "</div>";
        echo "</div>";
//      echo "<label class='cart-maxma-switch'>";
//            echo "<input type='checkbox'>";
//            echo "<span class='cms-slider'></span>";
//        echo "</label>";
    }
    else {
        if ($bonuses > 0) {
            echo "<div class='maxma-bonuses'>";
            echo "<div>Всего бонусных баллов: {$bonuses}</div>";
            echo "<div>При применении скидки бонусы не списываются.</div>";
            echo "</div>";
        }
        else {
            echo "<div class='maxma-bonuses'>";
            echo "<div>Всего бонусных баллов: 0</div>";
            echo "</div>";
        }
    }
}
add_action('woocommerce_after_cart_table', 'maxma_add_bonuses_range');



?>