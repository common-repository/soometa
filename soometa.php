<?php
/*
Plugin Name: Metta
Plugin URI: http://www.metta.io
Description: This plugin enables you to create immersive multimedia stories in minutes using videos, pictures, text paragraphs and audio form the web.
Version: 1.8
Author: Metta
License: GPL2
*/

/*  Copyright 2012  Dragontape Ltd  (email : info@dragontape.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
            
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
                            
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

register_activation_hook(__FILE__, 'soometa_activate');
add_action( 'admin_init', 'soometa_add_settings' );
add_shortcode('soometa', 'soometa_shortcode_handler');
add_action( 'media_upload_soometa', 'media_upload_soometa_new' );
add_action( 'media_upload_soometa_new', 'media_upload_soometa_new' );
add_action( 'media_upload_soometa_old', 'media_upload_soometa_old' );

function soometa_activate() {

    if(get_option('soometa-apikey', '') == '') {

	$msg = array(
	    'uname' => wp_hash(parse_url( get_option('home'), PHP_URL_HOST )),
	    'name' => get_option( 'blogname'),
	    'email' => get_option( 'admin_email'),
	    'desc' => get_option( 'blogdescription')
	);

        if( !class_exists( 'WP_Http' ) )
    	    include_once( ABSPATH . WPINC. '/class-http.php' );
	$request = new WP_Http;
	$result = $request->request( "http://api.metta.io/apiv3/user/register", array( 'method' => 'POST', 'body' => $msg ) );
	$resp = json_decode( $result["body"], true);
	if($resp != NULL && !isset($resp["error"])) {
	    update_option( 'soometa-apikey', $resp["token"], '', false);
	    update_option( 'soometa-uid', $resp["uid"]);
	} else {
	    //wp_die('http error '.json_encode($result));
	}
    }
}

function soometa_shortcode_handler($atts) {
     extract(shortcode_atts(array(
          'id' => '',
          'width' => '600',
	  'height' => '424'
     ), $atts));

    return '<iframe src="http://www.metta.io/stories/'.$id.'#e" width="'.$width.'" height="'.$height.'" frameborder=0 mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>';
}

function soometa_enqueue($hook) {

    if($hook == 'media-upload-popup' && $_REQUEST['type']=="soometa") {
	wp_enqueue_script( 'soometa-iframe' );
    }
    wp_enqueue_style( 'soometa-iframe-css' );
}

function soometa_add_settings() {
    add_filter('media_buttons', 'soometa_add_media_button');
    add_filter('media_upload_tabs', 'soometa_upload_tab');
    add_action( 'admin_enqueue_scripts', 'soometa_enqueue' );
    wp_register_style( 'soometa-iframe-css', plugins_url('/css/popup.css', __FILE__) );
    wp_register_script( 'soometa-iframe', plugins_url('/js/soometa-iframe.js', __FILE__) );
}

function soometa_upload_tab($tabs) {
    if(isset($_REQUEST['type']) && $_REQUEST['type']=="soometa") {
	$tabs = array();
	$tabs['soometa_new'] = "New story";
	$tabs['soometa_old'] = "Published stories";
    }
    return $tabs;
}

function soometa_add_media_button() {
    echo "<a href='media-upload.php?type=soometa&tab=soometa_new&TB_iframe=1&width=900' class='thickbox' title='Soometa'><img src='".plugins_url('/images/icon-wp-insertsoometa.png', __FILE__)."'</img></a>";
}

function media_upload_soometa_new() {
    global $errors;
    return wp_iframe('media_upload_soometa_new_tab', $errors );
}

function media_upload_soometa_old() {
    global $errors;
    return wp_iframe('media_upload_soometa_old_tab', $errors );
}

function media_upload_soometa_new_tab() {
    media_upload_header();
    $token = get_option('soometa-apikey');
    echo '<iframe class="soometa-iframe" src="https://metta.io/login?utk='.$token.'&r='.rawurlencode('http://www.metta.io/create?emb=wp').'"></iframe>';
}

function media_upload_soometa_old_tab() {
    media_upload_header();
    $uid = get_option('soometa-uid');
    echo '<iframe class="soometa-iframe" src="https://metta.io/login?utk='.$token.'&r='.rawurlencode('http://metta.io/'.$uid.'?emb=wp').'"></iframe>';
}

?>
