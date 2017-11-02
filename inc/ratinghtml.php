<?php
global $wpdb;

$frm = '';

$table_name = $wpdb->prefix.'v5_star_ratings';

$id = get_the_ID();
$average = $wpdb->get_var( "SELECT AVG(rating) FROM $table_name WHERE post_id = '$id'" );
$avg = round( $average, 1, PHP_ROUND_HALF_UP);
$frm .= '<div class="rating-container">
    <div class="mn_basic_rating" data-average="'.$avg.'" data-id="'.$id.'"></div>
    <p class="mnstr_msg" id="mnstr_msg'.$id.'"></p>
</div>';
