<?php
$parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
require_once($parse_uri[0] . 'wp-load.php');

global $wpdb;

$aResponse['error'] = false;
$aResponse['message'] = '';

$table_name = $wpdb->prefix . 'v5_star_ratings';

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'rating') {
        $id = intval($_POST['idBox']);
        $rate = floatval($_POST['rate']);

        $current_user = wp_get_current_user();
        $userIp = $_SERVER['REMOTE_ADDR'];
        $userEmail = '';
        if ((int) $current_user->ID > 0) {
            $userEmail = $current_user->user_email;
        }

        if (get_option('mn_star_rating_overrite') == '1') {
            if (get_option('mn_star_rate_user_data') == 'email') {
                $wpdb->get_results("SELECT * FROM $table_name WHERE post_id = '$id' AND user_id = '$userEmail'");
				$date = date("Y-m-d H:i:s");
				if($wpdb->num_rows >0){
                    $wpdb->query("UPDATE $table_name SET rating = '$rate', submit_date = '$date', `user_ip` = '$userIp' WHERE post_id = '$id' AND user_id = '$userEmail'");
					$aResponse['message'] = 'Your rate has been successfuly recorded. Thanks for your rate :)';
				}else{
					$wpdb->query("INSERT INTO $table_name SET post_id = '$id', user_id = '$userEmail', rating = '$rate', submit_date = '$date', `user_ip` = '$userIp'");
					$aResponse['message'] = 'Your rate has been successfuly recorded. Thanks for your rate :)';
				}
			}else{
				$wpdb->get_results("SELECT * FROM $table_name WHERE post_id = '$id' AND `user_ip` = '$userIp'");
				$date = date("Y-m-d H:i:s");
				if($wpdb->num_rows >0){
					$wpdb->query("UPDATE $table_name SET rating = '$rate', submit_date = '$date' WHERE post_id = '$id' AND `user_ip` = '$userIp'");
					$aResponse['message'] = 'Your rate has been successfuly recorded. Thanks for your rate :)';
				}else{
					$wpdb->query("INSERT INTO $table_name SET post_id = '$id', user_id = '$userEmail', rating = '$rate', submit_date = '$date', `user_ip` = '$userIp'");
					$aResponse['message'] = 'Your rate has been successfuly recorded. Thanks for your rate :)';
				}
			}
		}elseif(get_option('mn_star_rating_overrite') == '0'){
			if(get_option('mn_star_rate_user_data') == 'email'){
				$wpdb->get_results("SELECT * FROM $table_name WHERE post_id = '$id' AND user_id = '$userEmail'");
				$date = date("Y-m-d H:i:s");
				if($wpdb->num_rows >0){
					$aResponse['message'] = 'You can\'t rate this anymore';
					$aResponse['error'] = true;
				}else{
					$wpdb->query("INSERT INTO $table_name SET post_id = '$id', user_id = '$userEmail', rating = '$rate', submit_date = '$date', `user_ip` = '$userIp'");
					$aResponse['message'] = 'Your rate has been successfuly recorded. Thanks for your rate :)';
				}
			}else{
				$wpdb->get_results("SELECT * FROM $table_name WHERE post_id = '$id' AND `user_ip` = '$userIp'");
				$date = date("Y-m-d H:i:s");
				if($wpdb->num_rows >0){
					$aResponse['message'] = 'You can\'t rate this anymore';
					$aResponse['error'] = true;
				}else{
					$wpdb->query("INSERT INTO $table_name SET post_id = '$id', user_id = '$userEmail', rating = '$rate', submit_date = '$date', `user_ip` = '$userIp'");
					$aResponse['message'] = 'Your rate has been successfuly recorded. Thanks for your rate :)';
				}
			}
		}
		echo json_encode($aResponse);
	}
	else
	{
		$aResponse['error'] = true;
		$aResponse['message'] = '"action" post data not equal to \'rating\'';
		echo json_encode($aResponse);
	}
}
else
{
	$aResponse['error'] = true;
	$aResponse['message'] = '$_POST[\'action\'] not found';
	echo json_encode($aResponse);
}