<?php
/*
Plugin Name: Yahoo Japan Login and Autofill for Woocommerce
Plugin URI: https://mark.yokohama/plugins/yahoojapan
Description: Allow customers to log-in using Yahoo Japan in Wordpress / Woocommerce 
Version: 1.0
Requires at least: 4.3
Tested up to: 5.2.3
WC requires at least: 3.0
WC tested up to: 3.7.0
Author: Mark Joseph
Author URI: http://mark.yokohama
*/
define('MDY_YAHOO_VER', '1.0');
define('MDY_YAHOO_DIR', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) . '/');
define('MDY_YAHOO_URL', plugins_url() . "/" . plugin_basename( dirname(__FILE__) ) . '/');
define('MDY_YAHOO_BASE', plugin_basename( __FILE__));

if(!has_action( 'woocommerce_before_checkout_billing_form', 'mdy_checkout_autofill_buttons' )) {
	add_action('woocommerce_before_checkout_billing_form','mdy_checkout_autofill_buttons');
}

if(!has_action( 'woocommerce_login_form_start', 'mdy_login_buttons' )) {
	add_action('woocommerce_login_form_start', 'mdy_login_buttons');
}

add_action('mdy_checkout_autofill', 'mdy_yahoo_button');
add_action('mdy_login', 'mdy_yahoo_login_button');
add_action('wp_enqueue_scripts', 'mdy_yahoo_scripts');
add_action('wp_ajax_mdy_yahoo_login', 'mdy_yahoo_login_authorize');
add_action('wp_ajax_nopriv_mdy_yahoo_login', 'mdy_yahoo_login_authorize');

function mdy_yahoo_scripts() {
	wp_enqueue_script( 'mdy_yahoo_js', MDY_YAHOO_URL . 'yahoojapan-woocheckout.js', array('jquery'), false, true, 3.6 );
}

function mdy_checkout_autofill_buttons() {
	if(is_user_logged_in()) return true;
	echo '<div style="padding: 5px;">';
	do_action('mdy_checkout_autofill');
	echo '</div>';
}

function mdy_login_buttons() {
	if(is_user_logged_in()) return true;
	echo '<div style="padding: 5px;">';
	do_action('mdy_login');
	echo '</div>';
}


function mdy_yahoo_button() {
    ?>
        <span class="yconnectLoad mdy-buttons"></span>
    <?php
}

function mdy_yahoo_login_button() {
	?>
        <span class="yconnectLogin mdy-buttons"></span>
    <?php
}

function mdy_yahoo_login_authorize() {
	$code = isset($_GET['?code']) ? $_GET['?code'] : false;
	$res = wp_remote_post( 'https://auth.login.yahoo.co.jp/yconnect/v2/token',
		array(
			'method' => 'POST',
			'timeout' => 30,
			'headers' => array( 
				'Host' => 'auth.login.yahoo.co.jp',
				'Content-type' => 'application/x-www-form-urlencoded'
			),
			'body' => http_build_query(array(
				'grant_type' => 'authorization_code',
				'client_id' => 'dj00aiZpPXZBMjA3R1Z5dURhaSZzPWNvbnN1bWVyc2VjcmV0Jng9Nzg-',
				'client_secret' => 'S3pigOFxzCuDVdL3udINWLgtkZAM1ZdM7VO4xscl',
				'code' => $code,
				'redirect_uri' => 'http://vccw.test/wp-admin/admin-ajax.php?action=mdy_yahoo_login&'
			))
		)
	);

	if($res['response']['code'] == 200 && $res['response']['message'] = 'OK') {
		$body = json_decode( $res['body']);
		if($body->access_token) {
			$res2 = wp_remote_get( 'https://userinfo.yahooapis.jp/yconnect/v2/attribute?access_token=' . $body->access_token);

			if($res2['response']['code'] == 200 && $res2['response']['message'] = 'OK') {
				$body2 = json_decode( $res2['body']);
				$email = $body2->email;

				if(email_exists( $email )) {
					$user = get_user_by( 'email', $email );
					$user_id = $user->ID;
				} else {
					$password = wp_generate_password( 12, true );
					$user_id = wp_create_user ( $email, $password, $email );

					$to_update = array('ID' => 'user_id');
					if(isset($body2->given_name)) {
						$to_update['first_name'] = $body2->given_name;
						update_user_meta( $user_id, 'billing_first_name', $body2->given_name);
					}

					if(isset($body2->family_name)) {
						$to_update['last_name'] = $body2->family_name;
						update_user_meta( $user_id, 'billing_last_name', $body2->family_name);
					}

					update_user_meta( $user_id, 'billing_email', $email  );
					if(isset($body2->address->locality)) update_user_meta( $user_id, 'billing_city', $body2->address->locality );
					if(isset($body2->address->country)) update_user_meta( $user_id, 'billing_country', strtoupper($body2->address->country));
					if(isset($body2->address->postal_code)) update_user_meta( $user_id, 'billing_postcode', strtoupper($body2->address->postal_code));
					if(isset($body2->address->region)) update_user_meta( $user_id, 'billing_state', mdy_pref_to_code($body2->address->region));

					wp_update_user($to_update);
				}

				wp_clear_auth_cookie();
			    wp_set_current_user ( $user_id );
			    wp_set_auth_cookie  ( $user_id );

			    $redirect_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : user_admin_url();
			    wp_safe_redirect( $redirect_to );
			}
		} 
	}

	exit();
}

function mdy_pref_to_code($pref) {
	$prefs = array(
		["北海道", "JP01", "Hokkaido"],
        ["青森県", "JP02", "Aomori"],
        ["岩手県", "JP03", "Iwate"],
        ["宮城県", "JP04", "Miyagi"],
        ["秋田県", "JP05", "Akita"],
        ["山形県", "JP06", "Yamagata"],    
        ["福島県", "JP07", "Fukushima"],
        ["茨城県", "JP08", "Ibaraki"],
        ["栃木県", "JP09", "Tochigi"],
        ["群馬県", "JP10", "Gunma"],
        ["埼玉県", "JP11", "Saitama"],
        ["千葉県", "JP12", "Chiba"],
        ["東京都", "JP13", "Tokyo"],
        ["神奈川県","JP14", "Kanagawa"],
        ["新潟県", "JP15", "Niigata"],
        ["富山県", "JP16", "Toyama"],
        ["石川県", "JP17", "Ishikawa"],
        ["福井県", "JP18", "Fukui"],
        ["山梨県", "JP19", "Yamanashi"],
        ["長野県", "JP20", "Nagano"],
        ["岐阜県", "JP21", "Gifu"],
        ["静岡県", "JP22", "Shizuoka"],
        ["愛知県", "JP23", "Aichi"],
        ["三重県", "JP24", "Mie"],
        ["滋賀県", "JP25", "Shiga"],
        ["京都府", "JP26", "Kyoto"],
        ["大阪府", "JP27", "Osaka"],
        ["兵庫県", "JP28", "Hyogo"],
        ["奈良県", "JP29", "Nara"],
        ["和歌山県","JP30", "Wakayama"],
        ["鳥取県", "JP31", "Tottori"],
        ["島根県", "JP32", "Shimane"],
        ["岡山県", "JP33", "Okayama"],
        ["広島県", "JP34", "Hiroshima"],
        ["山口県", "JP35", "Yamaguchi"],
        ["徳島県", "JP36", "Tokushima"],
        ["香川県", "JP37", "Kagawa"],
        ["愛媛県", "JP38", "Ehime"],
        ["高知県", "JP39", "Kochi"],
        ["福岡県", "JP40", "Fukuoka"],
        ["佐賀県", "JP41", "Saga"],
        ["長崎県", "JP42", "Nagasaki"],
        ["熊本県", "JP43", "Kumamoto"],
        ["大分県", "JP44", "Oita"],
        ["宮崎県", "JP45", "Miyazaki"],
        ["鹿児島県","JP46", "Kagoshima"],
        ["沖縄県", "JP47", "Okinawa"]
	);

	foreach($prefs as $p) {
		if($p[0] == $pref) {
			return $p[1];
		}
	}
}