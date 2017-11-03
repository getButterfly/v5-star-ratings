<?php
/*
Plugin Name: V5 Star Ratings
Plugin URI: https://getbutterfly.com/downloads/v5-star-ratings
Description: Flexible star ratings plugin with multiple options for appearance and behaviour.
Version: 0.9.1
Author: getButterfly
Author URI: https://getbutterfly.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: v5-star-rating

Copyright 2017 Ciprian Popescu (email: getbutterfly@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

register_activation_hook( __FILE__, 'install_v5_star_ratings' ); 
//register_deactivation_hook(__FILE__, 'uninstall_v5_star_ratings');
register_uninstall_hook(__FILE__, 'uninstall_v5_star_ratings');

//add_action('init', 'install_v5_star_ratings');

define('V5_PLUGIN_URL', WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)));
define('V5_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));
define('V5_PLUGIN_FILE_PATH', WP_PLUGIN_DIR . '/' . plugin_basename(__FILE__));

function install_v5_star_ratings() {
    global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $tableName = $wpdb->prefix . "v5_star_ratings";
    $sql = "CREATE TABLE IF NOT EXISTS $tableName (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `post_id` bigint(20) NOT NULL,
        `user_id` varchar(128) NOT NULL,
        `user_ip` varchar(32) NOT NULL,
        `rating` varchar(8) NOT NULL,
        `submit_date` varchar(32) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";

    dbDelta($sql);
    maybe_convert_table_to_utf8mb4($tableName);

    add_option('mn_rating_enable_post', 0);
    add_option('mn_rating_enable_pages', 0);
    add_option('mn_rating_enable_images', 0);
    add_option('mn_no_of_star', 5);
    add_option('mn_star_size', 'big');
    add_option('mn_star_maximum_rating', 5);
    add_option('mn_star_hover_color', '#F62929');
    add_option('mn_star_rating_color', '#F4C239');
    add_option('mn_star_rating_overrite', 0);
    add_option('mn_star_rate_user_data', 'ip');
}

function uninstall_v5_star_ratings() {
    global $wpdb;

    $tableName = $wpdb->prefix . "v5_star_ratings";
    $sql = "DROP TABLE IF EXISTS $tableName;";
    $wpdb->query($sql);

    delete_option('mn_rating_enable_post');
    delete_option('mn_rating_enable_pages');
    delete_option('mn_rating_enable_images');
    delete_option('mn_no_of_star');
    delete_option('mn_star_size');
    delete_option('mn_star_maximum_rating');
    delete_option('mn_star_hover_color');
    delete_option('mn_star_rating_color');
    delete_option('mn_star_rating_overrite');
    delete_option('mn_star_rate_user_data');
}

add_action('admin_init', 'mn_star_rating_reg_setting');
function mn_star_rating_reg_setting() {
    register_setting('mn_star_rating_options', 'mn_rating_enable_post');
    register_setting('mn_star_rating_options', 'mn_rating_enable_pages');
    register_setting('mn_star_rating_options', 'mn_rating_enable_images');
    register_setting('mn_star_rating_options', 'mn_no_of_star');
    register_setting('mn_star_rating_options', 'mn_star_size');
    register_setting('mn_star_rating_options', 'mn_star_maximum_rating');
    register_setting('mn_star_rating_options', 'mn_star_hover_color');
    register_setting('mn_star_rating_options', 'mn_star_rating_color');
    register_setting('mn_star_rating_options', 'mn_star_rating_overrite');
    register_setting('mn_star_rating_options', 'mn_star_rate_user_data');
}

add_action('init', 'init_mn_star_rating', 10);
function init_mn_star_rating() {
    if (is_admin()) {
        mnstr_load_file('mn_pagination-style', '/css/pagination.css');
        mnstr_load_file('mn_pagination-style1', '/css/grey.css');

        wp_enqueue_style('wp-color-picker');
    } else {
        global $user_ID, $current_user;
        $current_user = wp_get_current_user();

        $_SESSION['mn_cur_usr_email'] = $current_user->user_email;
		$_SESSION['mn_star_rating_overrite'] = get_option('mn_star_rating_overrite');
		$_SESSION['mn_star_rate_user_data'] = get_option('mn_star_rate_user_data');
		$_SESSION['rate_after_login'] = false;
		if ($_SESSION['mn_star_rate_user_data'] == 'email'){
			$_SESSION['rate_after_login'] = true;
		}
		mnstr_load_file( 'mnstr-jquery-rating', '/js/jRating.jquery.js', true );
		mnstr_load_file( 'mnstr-public-script', '/js/widget.js', true );
		mnstr_load_file( 'mnstr-public-style', '/css/widget.css' );
		mnstr_load_file( 'mnstr-rating-style', '/css/jRating.jquery.css' );
	}

    add_filter('the_content', 'mn_star_rating_html');
    function mn_star_rating_html($content) {
        global $wpdb;

        $table_name = $wpdb->prefix.'v5_star_ratings';

        $newContent = $content;

        $id = get_the_ID();
        $average = $wpdb->get_var("SELECT AVG(rating) FROM $table_name WHERE post_id = '$id'");
        $avg = round($average, 1, PHP_ROUND_HALF_UP);
        $frm = '<div class="rating-container">
            <div class="mn_basic_rating" data-average="'.$avg.'" data-id="'.$id.'"></div>
            <p class="mnstr_msg" id="mnstr_msg'.$id.'"></p>
        </div>';

        if (is_single() && get_option('mn_rating_enable_post') == '1') {
            $newContent = $frm . $content;
		}
        if (is_page() && get_option('mn_rating_enable_pages') == '1') {
            $newContent = $frm . $content;
        }

        if (get_option('imagepress')) {
            $ip_slug = get_imagepress_option('ip_slug');

            if (is_singular() && $ip_slug == get_post_type()) {
                if (get_option('mn_rating_enable_images') == '1') {
                    $newContent = $frm . $content;
                }
            }
        }

        return $newContent;
	}
	
	add_action('wp_footer', 'mnstr_overlaymsg_html', 100);
	function mnstr_overlaymsg_html(){
	?>
        <div class="mn_overlay_msg">
        	
        </div>
        <?php
	}
}

function mnstr_load_file( $name, $file_path, $is_script = false ) {
	global $wpdb;
	$url = plugins_url($file_path, __FILE__);
	$file = plugin_dir_path(__FILE__) . $file_path;

	if( file_exists( $file ) ) {
		if( $is_script ) {
            $loggedIn = false;
            if (is_user_logged_in()) {
                $loggedIn = true;
            }

            $rateAfterlogin = false;
            if (get_option('mn_star_rate_user_data') === 'email') {
                $rateAfterlogin = true;
            }

            wp_register_script( $name, $url, array('jquery') );
			wp_enqueue_script( $name );
			wp_localize_script( $name, 'mnsr_ajax', array(
				'pluginurl' => plugin_dir_url(__FILE__),
				'mn_rating_enable_post' => get_option('mn_rating_enable_post'),
				'mn_rating_enable_pages' => get_option('mn_rating_enable_pages'),
				'mn_rating_enable_images' => get_option('mn_rating_enable_images'),
				'mn_no_of_star' => get_option('mn_no_of_star'),
				'mn_star_size' => get_option('mn_star_size'),
				'mn_star_maximum_rating' => get_option('mn_star_maximum_rating'),
				'mn_star_hover_color' => get_option('mn_star_hover_color'),
				'mn_star_rating_color' => get_option('mn_star_rating_color'),
				'mn_loggedin' => $loggedIn,
				'rate_after_login' => $rateAfterlogin
            ));
		} else {
			wp_register_style( $name, $url );
			wp_enqueue_style( $name );
		} // end if
	} // end if
} // end mnscm_load_file

/**
 *  Admin Panel Form settings
 */
add_action( 'admin_menu', 'register_mnstr_menu_page' );
function register_mnstr_menu_page() {
	add_menu_page('V5 Star Ratings', 'V5 Star Ratings', 'manage_options', 'mn_rating_menu', 'mn_star_rating_setting_html', 'dashicons-star-half'); 
	add_submenu_page('mn_rating_menu', 'V5 Star Ratings', 'Settings', 'manage_options', 'mn_rating_menu', 'mn_star_rating_setting_html');
	add_submenu_page('mn_rating_menu', 'V5 Star Ratings', 'Ratings Summary', 'manage_options', 'mn_rating_data_html', 'mn_rating_data_html');
}

function mn_star_rating_setting_html() {
    ?>
    <div class="wrap">
        <h1><?php echo __("V5 Star Ratings", 'v5-star-ratings'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('mn_star_rating_options'); ?>
            <?php do_settings_sections('mn_star_rating_options'); ?>

            <h2><?php _e('General Settings', 'v5-star-ratings'); ?></h2>
            <p>Set post types, permissions, appearance and general behaviour.</p>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label>Show star rating for <code>post</code></label></th>
                        <td>
                            <input type="radio" name="mn_rating_enable_post" id="mn_rating_enable_post" value="1" <?php echo (get_option('mn_rating_enable_post') == '1') ? 'checked' : ''; ?>> <label for="mn_rating_enable_post">Yes</label>&nbsp;
                            <input type="radio" name="mn_rating_enable_post" id="mn_rating_enable_post1" value="0" <?php echo (get_option('mn_rating_enable_post') == '0') ? 'checked' : ''; ?>> <label for="mn_rating_enable_post1">No</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Show star rating for <code>page</code></label></th>
                        <td>
                            <input type="radio" name="mn_rating_enable_pages" id="mn_rating_enable_pages" value="1" <?php echo (get_option('mn_rating_enable_pages') == '1') ? 'checked' : ''; ?>> <label for="mn_rating_enable_pages">Yes</label>&nbsp;
                            <input type="radio" name="mn_rating_enable_pages" id="mn_rating_enable_pages1" value="0" <?php echo (get_option('mn_rating_enable_pages') == '0') ? 'checked' : ''; ?>> <label for="mn_rating_enable_pages1">No</label>
                        </td>
                    </tr>
                    <?php
                    if (get_option('imagepress')) {
                        $ip_slug = get_imagepress_option('ip_slug');
                        ?>
                        <tr>
                            <th scope="row"><label>Show star rating for <code><?php echo $ip_slug; ?></code></label></th>
                            <td>
                                <input type="radio" name="mn_rating_enable_images" id="mn_rating_enable_images" value="1" <?php echo (get_option('mn_rating_enable_images') == '1') ? 'checked' : ''; ?>> <label for="mn_rating_enable_images">Yes</label>&nbsp;
                                <input type="radio" name="mn_rating_enable_images" id="mn_rating_enable_images1" value="0" <?php echo (get_option('mn_rating_enable_images') == '0') ? 'checked' : ''; ?>> <label for="mn_rating_enable_images1">No</label>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <th scope="row"><label>Rating permissions</label></th>
                        <td>
                            <select name="mn_star_rate_user_data" id="mn_star_rate_user_data">
                                <option value="ip" <?php echo (get_option('mn_star_rate_user_data') == 'ip') ? 'selected' : ''; ?>>Anyone can rate</option>
                                <option value="email" <?php echo (get_option('mn_star_rate_user_data') == 'email') ? 'selected' : ''; ?>>Only registered users can rate</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Number of stars</label></th>
                        <td>
                            <input type="number" name="mn_no_of_star" id="mn_no_of_star" value="<?php echo get_option('mn_no_of_star');?>" step="1" min="1" max="100" placeholder="5">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Maximum rating</label></th>
                        <td>
                            <input type="number" name="mn_star_maximum_rating" id="mn_star_maximum_rating" value="<?php echo get_option('mn_star_maximum_rating'); ?>" step="1" min="1" max="100" placeholder="5">
                            <br><small>This value should be identical to number of stars, but it is not mandatory.</small>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Size of stars</label></th>
                        <td>
                            <input type="radio" name="mn_star_size" id="mn_star_size" value="big" <?php echo (get_option('mn_star_size') == 'big') ? 'checked' : ''; ?>> <label for="mn_star_size"><img src="<?php echo plugin_dir_url(__FILE__);?>images/big.png" alt=""></label>&nbsp;
                            <input type="radio" name="mn_star_size" id="mn_star_size1" value="small" <?php echo (get_option('mn_star_size') == 'small') ? 'checked' : ''; ?>> <label for="mn_star_size1"><img src="<?php echo plugin_dir_url(__FILE__);?>images/sml.png" alt=""></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Rating colour</label></th>
                        <td>
                            <input type="text" name="mn_star_rating_color" id="mn_star_rating_color" value="<?php echo get_option('mn_star_rating_color'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Average rating colour</label></th>
                        <td>
                            <input type="text" name="mn_star_hover_color" id="mn_star_hover_color" value="<?php echo get_option('mn_star_hover_color'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Allow users to override own ratings</label></th>
                        <td>
                            <input type="radio" name="mn_star_rating_overrite" id="mn_star_rating_overrite" value="1" <?php echo (get_option('mn_star_rating_overrite') == '1') ? 'checked' : '';?>> <label for="mn_star_rating_overrite">Yes</label>&nbsp;
                            <input type="radio" name="mn_star_rating_overrite" id="mn_star_rating_overrite1" value="0" <?php echo (get_option('mn_star_rating_overrite') == '0') ? 'checked' : '';?>> <label for="mn_star_rating_overrite1">No</label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="submitbtn">
                <?php submit_button(); ?>
            </div>
        </form>
        <script>
		jQuery(document).ready(function(mnstr) {
            mnstr("#mn_star_rating_color, #mn_star_hover_color").wpColorPicker();
		});
        </script>
    </div>
    <?php
}



function mn_rating_data_html() {
    global $wpdb;

    $table_name = $wpdb->prefix . "v5_star_ratings";
    $total_rows = $wpdb->get_row("SELECT * FROM `$table_name`");

    include_once 'inc/pagination.php';

    $page = (int) (!isset($_GET["sheet"]) ? 1 : $_GET["sheet"]);
    $limit = 10;
    $startpoint = ($page * $limit) - $limit;

    $statement = "`$table_name`";
    ?>
    <h1>Ratings Summary</h1>
    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
                <th width="10%">#</th>
                <th width="30%">Title</th>
                <th width="15%">Average Rating</th>
                <th width="15%">Total Rating</th>
                <th width="15%">Min/Max</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th width="10%">#</th>
                <th width="30%">Title</th>
                <th width="15%">Average Rating</th>
                <th width="15%">Total Rating</th>
                <th width="15%">Min/Max</th>
            </tr>
        </tfoot>
        <tbody>
            <?php
            $result = $wpdb->get_results("SELECT DISTINCT post_id FROM $table_name LIMIT {$startpoint} , {$limit}");
            foreach ($result as $row) {
                $average = $wpdb->get_var("SELECT AVG(rating) FROM $table_name WHERE post_id = '$row->post_id'");
                $avg = round($average, 1, PHP_ROUND_HALF_UP);
                $total = $wpdb->get_var("SELECT SUM(rating) FROM $table_name WHERE post_id = '$row->post_id'");
                $count = $wpdb->get_var("SELECT COUNT(rating) FROM $table_name WHERE post_id = '$row->post_id'");
                $min = $wpdb->get_var("SELECT MIN(rating) FROM $table_name WHERE post_id = '$row->post_id'");
                $max = $wpdb->get_var("SELECT MAX(rating) FROM $table_name WHERE post_id = '$row->post_id'");
                ?>
                <tr id="<?php echo $row->post_id; ?>">
                    <td><?php echo $row->post_id; ?></td>
                    <td><?php echo get_the_title($row->post_id); ?></td>
                    <td><?php echo $avg; ?></td>
                    <td><?php echo $total . ' / ' . $count.' votes'; ?></td>
                    <td>Max: <?php echo $max; ?><br>Min: <?php echo $min; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
    echo v5_pagination($statement, $limit, $page, $url = '?page=mn_rating_data_html&');
}

add_shortcode('v5-star-ratings', 'vote5_star_rating');
add_shortcode('v5-star-ratings-widget', 'vote5_star_rating_widget');

function vote5_star_rating() {
    global $wpdb;

    $out = '';

    $table_name = $wpdb->prefix.'v5_star_ratings';

    $id = get_the_ID();
    $average = $wpdb->get_var( "SELECT AVG(rating) FROM $table_name WHERE post_id = '$id'" );
    $avg = round( $average, 1, PHP_ROUND_HALF_UP);
    $out .= '<div class="rating-container">
        <div class="mn_basic_rating" data-average="'.$avg.'" data-id="'.$id.'"></div>
        <p class="mnstr_msg" id="mnstr_msg'.$id.'"></p>
    </div>';

    return $out;
}

function vote5_star_rating_widget() {
    global $wpdb;

    $out = '<ul>';
        $result = $wpdb->get_results("SELECT post_id, AVG(rating) AS post_average FROM " . $wpdb->prefix . "v5_star_ratings GROUP BY post_id ORDER BY post_average DESC LIMIT 10");
        foreach ($result as $row) {
            $out .= '<li><a href="' . get_permalink($row->post_id) . '">' . get_the_title($row->post_id) . '</a> (' . number_format($row->post_average, 2) . ')</li>';
        }
    $out .= '</ul>';

    return $out;
}
