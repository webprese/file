<?php 
wp_enqueue_style('thickbox'); 
wp_enqueue_script('thickbox');

//new additional
function wpcr_view_reviews_ajax($page,$task,$id){
	global $wpdb, $current_user;
	global $WPCustomerReviews, $WPCustomerReviewsAdmin;
	
	$options = get_option('wpcr_options');
	$custom_fields_options=$options['field_custom'];

	$error="";
	$success="";
	if(isset($_POST["submit"])){
		$custom_fields_update=Array();
		foreach($custom_fields_options as $key => $value){
			if(isset($_POST["custom_".$key])){
				$custom_fields_update[$value]=$_POST["custom_".$key];
			}
		}
		if(count($custom_fields_update)>0)
			$custom_fields_update=", custom_fields='".@serialize($custom_fields_update)."'";
		else
			$custom_fields_update='';
	
		//we show editing form here.
		$date_time=$_POST["date_time"];
		$reviewer_name=$_POST["reviewer_name"];
		$reviewer_email=$_POST["reviewer_email"];
		$reviewer_ip=$_POST["reviewer_ip"];
		$review_title=$_POST["review_title"];
		$review_text=$_POST["review_text"];
		$review_rating=$_POST["review_rating"];
		$reviewer_url=$_POST["reviewer_url"];
		$page_id=$_POST["page_id"];
		$strSQL="	UPDATE `".$WPCustomerReviews->dbtable."` SET 
					date_time='".mysql_real_escape_string($date_time)."',
					reviewer_name='".mysql_real_escape_string($reviewer_name)."',
					reviewer_email='".mysql_real_escape_string($reviewer_email)."',
					reviewer_ip='".mysql_real_escape_string($reviewer_ip)."',
					review_title='".mysql_real_escape_string($review_title)."',
					review_text='".mysql_real_escape_string($review_text)."',
					review_rating='".mysql_real_escape_string($review_rating)."',
					reviewer_url='".mysql_real_escape_string($reviewer_url)."',
					page_id='".$page_id."'".$custom_fields_update."
					WHERE id=".$id."
				";
		//echo $strSQL;
		if($wpdb->query($strSQL)!==false){
			$success="Success Update";
		}
		else{
			$error="Error Failed Update";
		}
		$WPCustomerReviewsAdmin->force_update_cache();
	}
	$query = "SELECT 
				id,
				date_time,
				reviewer_name,
				reviewer_email,
				reviewer_ip,
				review_title,
				review_text,
				review_rating,
				reviewer_url,
				status,
				page_id,
				custom_fields
				FROM `".$WPCustomerReviews->dbtable."` WHERE id='".intval($id)."'"; 
				
	$reviews = $wpdb->get_results($query);
	if(is_array($reviews)){
		//we show editing form here.
		$date_time=$reviews[0]->date_time;
		$reviewer_name=$reviews[0]->reviewer_name;
		$reviewer_email=$reviews[0]->reviewer_email;
		$reviewer_ip=$reviews[0]->reviewer_ip;
		$review_title=$reviews[0]->review_title;
		$review_text=$reviews[0]->review_text;
		$review_rating=$reviews[0]->review_rating;
		$reviewer_url=$reviews[0]->reviewer_url;
		$page_id=$reviews[0]->page_id;
		$custom_fields=$reviews[0]->custom_fields;
		require_once("html/form-reviewer.php");
	}
	else{
		echo 'Error';
	}
}
function WPCustomerReviews_admin_ajax(){
	global $wpdb, $current_user;
	$page=@$_GET["page"];
	$task=@$_GET["task"];
	$id=@$_GET["r"];
	if ($current_user->user_level=="10"){
		switch ($page) {
			case "wpcr_view_reviews":
				wpcr_view_reviews_ajax($page,$task,$id);
				break;
		}
	}
	exit;
}

function wpcr_publish_extended_callback() {

	$options = get_option('wpcr_options');

	// load facebook platform
	include_once 'facebook-php-sdk/src/facebook.php';
	$fb = new Facebook(array(
	  'appId'  => $options['facebook_apiappid'],
	  'secret' => $options['facebook_apisecret'],
	));
	$params=Array("scope"=>Array("email","manage_pages","publish_stream"));
	$user = $fb->getUser();
	$accesstoken=$fb->getAccessToken();

	if(@$_GET["resetfb"]){
		//do nothing here
		$fb->destroySession();
		$options['facebook_apiuserid']="";
		$options['facebook_apisessionkey']="";
		update_option('wpcr_options',$options);
		echo '<script>location.href="options-general.php?page=wpcr_options&resetfbclear=1";</script>';
	}
	else{
		$refresh=0;
		if($user){
			if($options['facebook_apiuserid']=="" && @$_GET["resetfbclear"]!=1){
				$options['facebook_apiuserid']=$user;
				$refresh=1;
			}
		}
		if($accesstoken){
			if($options['facebook_apisessionkey']=="" && @$_GET["resetfbclear"]!=1){
				$options['facebook_apisessionkey']=$accesstoken;
				$refresh=1;
			}
		}
		if ($refresh==1 && @$_GET["updatefb"]!=1){
			update_option('wpcr_options',$options);
			echo '<script>location.href="options-general.php?page=wpcr_options&updatefb=1";</script>';
		}
	}
	if($options['facebook_apisessionkey']=="" && $options['facebook_apiuserid']==""){
	  $loginUrl = $fb->getLoginUrl($params);
	  $loginUrl = str_replace("%26resetfbclear%3D1","",$loginUrl);
	}

?><p><?php _e('In order for the Facebook Publish to be able to publish your posts automatically, you must grant some "Extended Permissions"
to the plugin.', 'wpcr'); ?></p>
<ul>
<li><?php _e('Offline Permission is needed to access your Page as if you were publishing manually.', 'wpcr'); ?><br /><span id="wpcr-offline-perm-check"></span></li>
<li><?php _e('Publish Permission is needed to publish stories to the stream automatically.', 'wpcr'); ?><br /><span id="wpcr-publish-perm-check"></span></li>
<?php if ($options['facebook_publishtopage']) { ?>
<li><?php _e('Fan Page Publish Permission is needed to publish stories to the Fan Page automatically.', 'wpcr'); ?><br /><span id="wpcr-fanpage-perm-check"></span></li>
<?php } ?>
</ul>
<?php
	if ($loginUrl){
		echo '<input type="button" class="button-primary" onclick="javascript:location.href=\''.$loginUrl.'\';" value="Grant Publish Permission" />';
	}
	if ($logoutUrl){
		echo '<input type="button" class="button-primary" onclick="javascript:location.href=\''.$logoutUrl.'\';" value="Remove Publish Permission" />';
	}
	echo '<br /><br /><input type="button" class="button-primary" onclick="javascript:location.href=\'options-general.php?page=wpcr_options&resetfb=1\';" value="Reset Permission" />';
?>
<?php if ($options['facebook_apiuserid'] && $options['facebook_apisessionkey']) {
	?><p><?php _e('User ID and Session Key found! Automatic publishing is ready to go!', 'wpcr'); ?></p><?php
} else {
	?><p><?php _e('Be sure to click the "Save Settings" button on this page after granting these permissions! This will allow us to save your user id and session key, for usage by the plugin when publishing posts to your profile and/or page.', 'wpcr'); ?></p><?php
} ?>
<?php
}

add_action('wp_ajax_custreview', 'WPCustomerReviews_admin_ajax');

//add_action('wp_enqueue_scripts','wpcr_featureloader');
function wpcr_featureloader() {
	if (@$_SERVER['HTTPS'] == 'on')
		wp_enqueue_script( 'fb-featureloader', 'https://ssl.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/'.get_locale(), array(), '0.4', false);
	else
		wp_enqueue_script( 'fb-featureloader', 'http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/'.get_locale(), array(), '0.4', false);
}

add_action('wp_footer','wpcr_add_base_js',20); // 20, to put it at the end of the footer insertions. sub-plugins should use 30 for their code
function wpcr_add_base_js() {
	$options = get_option('wpcr_options');
	//wpcr_load_api($options['facebook_apikey']);
};

function wpcr_load_api($key) {
?>
<script type="text/javascript">
FB_RequireFeatures(["XFBML"], function() {
	FB.init("<?php echo $key; ?>", "<?php echo home_url('/?xd_receiver=1&amp;time=').time(); ?>", <?php echo json_encode($sets); ?>);
});
</script>
<?php
}

add_action('admin_init', 'wpcrfb_admin_init',9); // 9 to force it first, subplugins should use default
function wpcrfb_admin_init(){
	$options = get_option('wpcr_options');
	if (empty($options['facebook_apisecret']) || empty($options['facebook_apiappid'])) {
		//add_action('admin_notices', create_function( '', "echo '<div class=\"error\"><p>".sprintf(__('Flowline Customer Reviews - Facebook Connect needs configuration information on its <a href="%s">settings</a> page.', 'sfc'), admin_url('options-general.php?page=wpcr_options'))."</p></div>';" ) );
	} else {
		wpcr_featureloader();
		add_action('admin_footer','wpcr_add_base_js',20);
	}
}

function wpcr_publishfeed($reviewid){
	global $wpdb, $wpcr_reviewratingtext,$WPCustomerReviews;

	$query = "SELECT 
				id,
				date_time,
				reviewer_name,
				reviewer_email,
				reviewer_ip,
				review_title,
				review_text,
				review_rating,
				reviewer_url,
				status,
				page_id,
				custom_fields
				FROM `".$WPCustomerReviews->dbtable."` WHERE id='".intval($reviewid)."'"; 
				
	$reviews = $wpdb->get_results($query);
	if(is_array($reviews)){
		$reviewer_name=$reviews[0]->reviewer_name;
		if($reviewer_name=="")
			$reviewer_name="Anonymous";
		$review_title=$reviews[0]->review_title;
		$review_text=$reviews[0]->review_text;
		$page_id=$reviews[0]->page_id;
		$permalink = get_permalink($page_id);
		$review_rating=intval($reviews[0]->review_rating)-1;
		if($review_rating<0)
			$review_rating=0;
		// load facebook platform
		$options = get_option('wpcr_options');
		$business_city=$options["business_city"];
		$business_state=$options["business_state"];  
		if(!empty($business_city))
			$business_city=', '.$business_city;
		if(!empty($business_state))
			$business_state=', '.$business_state;


		// load facebook platform
		include_once 'facebook-php-sdk/src/facebook.php';
		$fb = new Facebook(array(
		  'appId'  => $options['facebook_apiappid'],
		  'secret' => $options['facebook_apisecret'],
		));
		$params=Array("scope"=>Array("email","manage_pages","publish_stream","offline_access"));
		$user = $fb->getUser();
		$accesstoken=$fb->getAccessToken();
		$user=$options['facebook_apiuserid'];
		$accesstoken=$options['facebook_apisessionkey'];
		if($user && $accesstoken){
			$captionfb=$reviewer_name.''.$business_city.''.$business_state;
			if($reviewer_name=="Anonymous" && $options['custom_anonymous']==0){
				$reviewer_name="";
				$captionfb=substr($business_city.''.$business_state,2);
			}
			if($options['custom_citystatecountry']){
				//here we use custom settings based on the field defined.
				$key_city=$options['custom_city'];
				$key_country=$options['custom_country'];
				$key_state=$options['custom_state'];
				$value_city="";
				$value_country="";
				$value_state="";
				$captionfb="";
				if($key_city!="" || $key_country!="" || $key_state!=""){
					$custom_fields = @unserialize($reviews[0]->custom_fields);
					$custom_fields_options=$options['field_custom'];
					foreach($custom_fields_options as $key => $value){
						if(!empty($value)){
							if($key==$key_city){
								$value_city=$custom_fields[$value];
							}
							if($key==$key_country){
								$value_country=$custom_fields[$value];
							}
							if($key==$key_state){
								$value_state=$custom_fields[$value];
							}
						}
					}
					if($reviewer_name!="")
						$captionfb.=$reviewer_name.", ";
					if($value_city!="")
						$captionfb.=$value_city.", ";
					if($value_state!="")
						$captionfb.=$value_state.", ";
					if($value_country!="")
						$captionfb.=$value_country.", ";

					if(!empty($captionfb))
						$captionfb=substr($captionfb,0,-2);

					if(empty($captionfb)){
						if($reviewer_name=="Anonymous" && $options['custom_anonymous']==0){
							$captionfb=substr($business_city.''.$business_state,2);
						}
						else{
							$captionfb=$reviewer_name.''.$business_city.''.$business_state;
						}
					}
				}
			}
			$fan_pages = array();
			$access_token=$options['facebook_apisessionkey'];
			if($options['facebook_apipageid']!=""){
				$temp_pages = $fb->api('/'.$options['facebook_apiuserid'].'/accounts','GET',array('access_token'=>$options['facebook_apisessionkey']));
				if(count($temp_pages['data']) > 0)
				{
					foreach($temp_pages['data'] as $page)
					{
						if($page["category"] != "Application"){
							$fan_pages[$page['id']] = $page['access_token'];
							if($page['id']==$options['facebook_apipageid']){
								$access_token=$page['access_token'];
								break;
							}
						}
					}
				}
			}
			//print_r($fan_pages);
			$attachment = array( 
				'method' => 'POST',
				'access_token' => $access_token,
				'href' => "".$permalink."",
				'caption' => $captionfb, 
				'description' => "Rating: ".$wpcr_reviewratingtext[$review_rating].".&nbsp;&nbsp;".stripslashes($review_text)."", 
				'link' => $permalink,
				'name' => $review_title
			);
			//print_r($attachment);
			//exit;
			$request = new WP_Http;
			//if($options['facebook_apipageid']!="")
			//	$api_url = 'https://graph.facebook.com/'.$options['facebook_apipageid'].'/feed';
			//else
			$api_url = 'https://graph.facebook.com/me/feed';
			$response = $request->request($api_url, array('method' => 'POST', 'body' => $attachment, 'timeout' => FPP_REQUEST_TIMEOUT, 'sslverify' => false)); 
			if(is_array($response["errors"]) || strstr($response["body"],"error")){
				echo '<center><font style="font-weight:bold;color:#ff0000;">Failed to Publish to Facebook, Please contact your Administrator</font></center><br /><br />';
				print_r($response);
				exit;
			}
			else{
				echo '<center><font style="font-weight:bold;color:#0000ff;">success publish to Facebook.</font></center><br /><br />';
			}

		}
        // Publish:

		/*
		//include_once 'facebook-platform/facebook.php';
		//$fb=new Facebook($options['facebook_apikey'], $options['facebook_apisecret']);

		// to do this autopublish, we might need to switch users
		if ($options['facebook_apiuserid'] && $options['facebook_apisessionkey']) {
			$tempuser = $fb->user;
			$tempkey = $fb->api_client->session_key = $session_key;
			$fb->set_user($options['facebook_apiuserid'], $options['facebook_apisessionkey']);

			$action_links = null; # Define to your liking
			$message = '';
			$page_id = $options['facebook_apipageid'];
			
			$captionfb=$reviewer_name.''.$business_city.''.$business_state;

			if($reviewer_name=="Anonymous" && $options['custom_anonymous']==0){
				$reviewer_name="";
				$captionfb=substr($business_city.''.$business_state,2);
			}

			if($options['custom_citystatecountry']){
				//here we use custom settings based on the field defined.
				$key_city=$options['custom_city'];
				$key_country=$options['custom_country'];
				$key_state=$options['custom_state'];
				$value_city="";
				$value_country="";
				$value_state="";
				$captionfb="";
				if($key_city!="" || $key_country!="" || $key_state!=""){
					$custom_fields = @unserialize($reviews[0]->custom_fields);
					$custom_fields_options=$options['field_custom'];
					foreach($custom_fields_options as $key => $value){
						if(!empty($value)){
							if($key==$key_city){
								$value_city=$custom_fields[$value];
							}
							if($key==$key_country){
								$value_country=$custom_fields[$value];
							}
							if($key==$key_state){
								$value_state=$custom_fields[$value];
							}
						}
					}
					if($reviewer_name!="")
						$captionfb.=$reviewer_name.", ";
					if($value_city!="")
						$captionfb.=$value_city.", ";
					if($value_state!="")
						$captionfb.=$value_state.", ";
					if($value_country!="")
						$captionfb.=$value_country.", ";

					if(!empty($captionfb))
						$captionfb=substr($captionfb,0,-2);

					if(empty($captionfb)){
						if($reviewer_name=="Anonymous" && $options['custom_anonymous']==0){
							$captionfb=substr($business_city.''.$business_state,2);
						}
						else{
							$captionfb=$reviewer_name.''.$business_city.''.$business_state;
						}
					}
				}
			}
			$attachment = array( 'name' => "".$review_title."",
			'href' => "".$permalink."",
			'caption' => $captionfb, 
			'description' => ''.stripslashes($review_text).'',
			'properties' => array('Rating' => array('text' => ''.$wpcr_reviewratingtext[$review_rating].'', 'href' => "".get_option("siteurl")."")),
			);
			if( $fb->api_client->stream_publish($message, $attachment, $action_links, null, $page_id)){
				echo '<center><font style="font-weight:bold;color:#0000ff;">success publish to Facebook.</font></center><br /><br />';
			}
			else{
				echo '<center><font style="font-weight:bold;color:#ff0000;">Failed to Publish to Facebook, Please contact your Administrator</font></center><br /><br />';
			}

		} else {
			return; // safety net: if we don't have a user and session key, we can't publish properly.
		}
		*/
	}
}
//new additional

//twitter goes in here.
add_action('init','wpcr_stc_init');
function wpcr_stc_init() {
	// fast check for authentication requests on plugin load.
	if (session_id() == '') {
		session_start();
	}
	if(isset($_GET['stc_oauth_start'])) {
		wpcr_stc_oauth_start();
	}
	if(isset($_GET['oauth_token'])) {
		wpcr_stc_oauth_confirm();
	}
	$options = get_option('wpcr_options');
	if(($options['autotweet_token'] != @$_SESSION['wpcr_stc_acc_token']) || ($options['autotweet_secret'] != @$_SESSION['wpcr_stc_acc_secret'])){
		if(!empty($_SESSION['wpcr_stc_acc_token']))
			$options['autotweet_token'] = $_SESSION['wpcr_stc_acc_token'];
		if(!empty($_SESSION['wpcr_stc_acc_secret']))
			$options['autotweet_secret'] = $_SESSION['wpcr_stc_acc_secret'];
		if(!empty($_SESSION['wpcr_stc_acc_token']) || !empty($_SESSION['wpcr_stc_acc_secret']))
			update_option('wpcr_options', $options);
	}
}

function wpcr_stc_oauth_start() {
	$options = get_option('wpcr_options');
	if (empty($options['twitter_ckey']) || empty($options['twitter_csecret'])) return false;
	include_once "twitterOAuth.php";

	$to = new TwitterOAuth($options['twitter_ckey'], $options['twitter_csecret']);
	$tok = $to->getRequestToken();

	$token = $tok['oauth_token'];
	$_SESSION['wpcr_stc_req_token'] = $token;
	$_SESSION['wpcr_stc_req_secret'] = $tok['oauth_token_secret'];

	$_SESSION['wpcr_stc_callback'] = $_GET['loc'];
	$_SESSION['wpcr_stc_callback_action'] = $_GET['stcaction'];

	if ($_GET['type'] == 'authorize') $url=$to->getAuthorizeURL($token);
	else $url=$to->getAuthenticateURL($token);

	$options = get_option('wpcr_options');
	$options['autotweet_token']=$token;
	$options['autotweet_secret']=$tok['oauth_token_secret'];
	update_option('wpcr_options', $options);

	wp_redirect($url);
	exit;
}

function wpcr_stc_oauth_confirm() {
	$options = get_option('wpcr_options');
	if (empty($options['twitter_ckey']) || empty($options['twitter_csecret'])) return false;
	include_once "twitterOAuth.php";

	$to = new TwitterOAuth($options['twitter_ckey'], $options['twitter_csecret'], $_SESSION['wpcr_stc_req_token'], $_SESSION['wpcr_stc_req_secret']);

	$tok = $to->getAccessToken();

	$_SESSION['wpcr_stc_acc_token'] = $tok['oauth_token'];
	$_SESSION['wpcr_stc_acc_secret'] = $tok['oauth_token_secret'];

	$to = new TwitterOAuth($options['twitter_ckey'], $options['twitter_csecret'], $tok['oauth_token'], $tok['oauth_token_secret']);

	// this lets us do things actions on the return from twitter and such
	if ($_SESSION['wpcr_stc_callback_action']) {
		do_action('wpcr_stc_'.@$_SESSION['wpcr_stc_callback_action']);
		$_SESSION['wpcr_stc_callback_action'] = ''; // clear the action
	}

	$options = get_option('wpcr_options');
	$options['autotweet_token']=@$token;
	$options['autotweet_secret']=$tok['oauth_token_secret'];
	update_option('wpcr_options', $options);

	wp_redirect($_SESSION['wpcr_stc_callback']);
	exit;
}

// get the user credentials from twitter
function wpcr_stc_get_credentials($force_check = false) {
	// cache the results in the session so we don't do this over and over
	if (!$force_check && $_SESSION['wpcr_stc_credentials']) return $_SESSION['wpcr_stc_credentials'];

	$_SESSION['wpcr_stc_credentials'] = wpcr_stc_do_request('http://twitter.com/account/verify_credentials');

	return $_SESSION['wpcr_stc_credentials'];
}

// json is assumed for this, so don't add .xml or .json to the request URL
function wpcr_stc_do_request($url, $args = array(), $type = NULL) {

	if (@$args['acc_token']) {
		$acc_token = @$args['acc_token'];
		unset($args['acc_token']);
	} else {
		$acc_token = @$_SESSION['wpcr_stc_acc_token'];
	}

	if (@$args['acc_secret']) {
		$acc_secret = @$args['acc_secret'];
		unset($args['acc_secret']);
	} else {
		$acc_secret = @$_SESSION['wpcr_stc_acc_secret'];
	}

	$options = get_option('wpcr_options');
	if (empty($options['twitter_ckey']) || empty($options['twitter_csecret']) ||
		empty($acc_token) || empty($acc_secret) ) return false;

	include_once "twitterOAuth.php";

	$to = new TwitterOAuth($options['twitter_ckey'], $options['twitter_csecret'], $acc_token, $acc_secret);
	$json = $to->OAuthRequest($url.'.json', $args, $type);

	return json_decode($json);
}

add_action('admin_enqueue_scripts','wpcr_stc_anywhereloader');
function wpcr_stc_anywhereloader() {
	$options = get_option('wpcr_options');

	if (!empty($options['twitter_ckey'])) {
		@wp_enqueue_script( 'twitter-anywhere', "http://platform.twitter.com/anywhere.js?id={$options['consumer_key']}&v=1", array(), '1', false);
	}
}

// add the admin settings and such
add_action('admin_init', 'wpcr_stc_admin_init',9); // 9 to force it first, subplugins should use default
function wpcr_stc_admin_init(){
	$options = get_option('wpcr_options');
	$tw = wpcr_stc_get_credentials(true);
	if(@$options['autotweet_name'] != @$tw->screen_name && !empty($tw->screen_name)){
		$options['autotweet_name'] = @$tw->screen_name;
		update_option('wpcr_options', $options);
	}

	if (empty($options['twitter_ckey']) || empty($options['twitter_csecret']) || empty($options['autotweet_name'])) {
		//add_action('admin_notices', create_function( '', "echo '<div class=\"error\"><p>".sprintf('Flowline Customer Reviews - Twitter Connect needs configuration information on its <a href="%s">settings</a> page.', admin_url('options-general.php?page=wpcr_options'))."</p></div>';" ) );
	}
	wp_enqueue_script('jquery');
}


// display the admin options page
function wpcr_twitter_extended_callback() {
?>
	<div>
	<br /><br />
	<p><b>Publish Settings</b></p><br />
	Settings for the Twitter Publish plugin.<br />
	<?php wpcr_twitter_publish_auto_callback(); ?>
	</div>

<?php
}

function wpcr_stc_get_connect_button($action='', $type='authenticate') {
	$options = get_option('stc_options');
	return '<a href="'.home_url().'/?stc_oauth_start=1&stcaction='.urlencode($action).'&loc='.urlencode(wpcr_stc_get_current_url()).'&type='.urlencode($type).'">'.
		   '<img border="0" src="'.plugins_url('/images/Sign-in-with-Twitter-darker.png', __FILE__).'" />'.
		   '</a>';
}

function wpcr_stc_get_current_url() {
	// build the URL in the address bar
	$requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
	$requested_url .= $_SERVER['HTTP_HOST'];
	$requested_url .= $_SERVER['REQUEST_URI'];
	return $requested_url;
}

function wpcr_twitter_publish_auto_callback() {
	$options = get_option('wpcr_options');
	if (!$options['autotweet_flag']) $options['autotweet_flag'] = false;
	?>
	<p><label>Automatically Tweet on Publish: <input type="checkbox" name="autotweet_flag" value="1" <?php checked('1', $options['autotweet_flag']); ?> /></label></p>
	<?php
	$tw = wpcr_stc_get_credentials(true);
	if (@$tw->screen_name) echo "<p>Currently logged in as: <strong>{$tw->screen_name}</strong></p>";
	if(@$options['autotweet_name'] != @$tw->screen_name){
		$options['autotweet_name'] = @$tw->screen_name;
		update_option('wpcr_options', $options);
	}

	if ($options['autotweet_name']) {
		echo "<p>Autotweet set to Twitter User: <strong>{$options['autotweet_name']}</strong></p>";
	} else {
		echo "<p>Autotweet not set to a Twitter user.</p>";
	}
	echo '<p>To auto-publish new posts to any Twitter account, click this button and then log into that account to give the plugin access.</p><p>Authenticate for auto-tweeting: '.wpcr_stc_get_connect_button('publish_preauth', 'authorize').'</p>';
	echo '<p>Afterwards, you can use this button to log back into your own normal account, if you are posting to a different account than your normal one. </p><p>Normal authentication: '.wpcr_stc_get_connect_button('', 'authorize').'</p>';
}

function wpcr_stc_publish_automatic($reviewid) {
	global $wpdb, $wpcr_reviewratingtext,$WPCustomerReviews;

	// check options to see if we need to send to FB at all
	$options = get_option('wpcr_options');
	//print_r($options);
	if (!$options['autotweet_flag'] || !$options['autotweet_token'] || !$options['autotweet_secret']){
		echo '<center><font style="font-weight:bold;color:#ff0000;">failed update. please reset the session key and token</font></center>';
		return;
	}

	$query = "SELECT 
				id,
				date_time,
				reviewer_name,
				reviewer_email,
				reviewer_ip,
				review_title,
				review_text,
				review_rating,
				reviewer_url,
				status,
				page_id,
				custom_fields
				FROM `".$WPCustomerReviews->dbtable."` WHERE id='".intval($reviewid)."'"; 
				
	$reviews = $wpdb->get_results($query);
	if(is_array($reviews)){
		$reviewer_name=$reviews[0]->reviewer_name;
		$review_title=$reviews[0]->review_title;
		$review_text=$reviews[0]->review_text;
		$page_id=$reviews[0]->page_id;
		$permalink = get_permalink($page_id);
		$review_rating=intval($reviews[0]->review_rating)-1;
		if($review_rating<0)
			$review_rating=0;
		// load facebook platform
		$options = get_option('wpcr_options');
		$business_name=$options["business_name"];
		$business_city=$options["business_city"];
		$business_state=$options["business_state"];  
		if(!empty($business_city))
			$business_city=', '.$business_city;
		if(!empty($business_state))
			$business_state=', '.$business_state;
		// args to send to twitter
		$args=array();
		//set status length
		$twitterstatus = $reviewer_name.''.$business_city.''.$business_state;
		$totaltwitterlength = strlen($twitterstatus)+strlen($wpcr_reviewratingtext[$review_rating])+21;
		//set status length
		$args['status'] = $reviewer_name.' just rated '.$business_name.' services '.$wpcr_reviewratingtext[$review_rating].".";
		$args['acc_token'] = $options['autotweet_token'];
		$args['acc_secret'] = $options['autotweet_secret'];
		$resp = wpcr_stc_do_request('http://api.twitter.com/1/statuses/update',$args);
		if(!empty($resp->error))
			echo '<center><font style="font-weight:bold;color:#ff0000;">failed update. '.$resp->error.'</font></center>';
		else
			echo '<center><font style="font-weight:bold;color:#0000ff;">success publish to twitter.</font></center>';
	}
}
/*
function wpcr_linkedin_extended_callback(){

}
*/
?>