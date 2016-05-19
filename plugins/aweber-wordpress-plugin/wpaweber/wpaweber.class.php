<?php
class WPAweber
{
	var $version = 1.0;
	var $siteurl = '';
	var $plugindir = '';
	var $installed = false;
	var $post = array('register' => false, 'comment' => false);
	var $formid = '';
	var $unit = '';
	var $table = 'aweber';
	var $sub = false;
	var $email = '';
	var $name = '';
	var $registerterms = '';
	var $commentterms = '';
	var $showterms = array();
	var $defaultterms = array();

	function WPAweber() {
	}

	function hook() {
		// installed or not
		$this->installed = get_option('wpaweber_version') == $this->version;

		// get options
		$actions = explode(',', get_option('wpaweber_actions'));
		foreach ( $actions as $a )
			$this->post[$a] = true;
		$t = explode(',', get_option('wpaweber_showterms'));
		foreach ( $t as $h )
			$this->showterms[$h] = true;
		$t = explode(',', get_option('wpaweber_defaultterms'));
		foreach ( $t as $h )
			$this->defaultterms[$h] = true;
		$this->commentterms = get_option('wpaweber_commentterms');
		$this->registerterms = get_option('wpaweber_registerterms');
		$this->formid = get_option('wpaweber_formid');
		$this->unit = get_option('wpaweber_unit');

		// actions
		add_action('activate_wpaweber/wpaweber.php', array(&$this, 'install'));
		add_action('deactivate_wpaweber/wpaweber.php', array(&$this, 'uninstall'));

		if ( $this->installed ) {
		    add_action('admin_menu', array(&$this, 'adminMenu'));
			add_action('user_register', array(&$this, 'register'));
			add_action('wp_set_comment_status', array(&$this, 'commentStatus'), 10, 2);
			add_action('comment_post', array(&$this, 'commentPost'), 10, 2);
			add_action('comment_form', array(&$this, 'commentForm'));
			add_action('register_form', array(&$this, 'registerForm'));
			add_filter('comment_post_redirect', array(&$this, 'commentRedirect'));
			add_filter('pre_comment_content', array(&$this, 'checkCommentTerms'));
			add_filter('user_registration_email', array(&$this, 'checkRegisterTerms'));
			add_action('wp_footer', array(&$this, 'plicse_watermark'));
		}

		// wpaweber directories
		$this->plugindir = dirname(__file__) . '/';
		$this->siteurl = get_option('siteurl'); // . '/x.php';
    	$this->optionsurl = get_option('siteurl') . '/wp-admin/options-general.php?page=wpaweber';

		// db
		global $wpdb;
		$this->table = $wpdb->prefix . $this->table;
	}

	function plicse_watermark(){
		// Do Not Change!  Please leave the footer message intact to author's credit.  Thank you!
		echo 'Powered by <a href="http://www.gurucs.com/products/wordpress-aweber-plugin/">Aweber Wordpress Plugin</a> and <a href="http://www.netpassiveincome.com">Passive Income</a>.';
	}

	function commentForm($x = null) {
		if ( !$this->post['comment'] )
			return;

		if ( $this->commentterms ) {
			echo '<p><input type="checkbox" name="agree_terms" value="1" style="width: 0px;"';
			if ( $this->defaultterms['comment'] )
				echo ' checked="checked"';
			echo ' />';
			echo html_entity_decode($this->commentterms).'</p>';
		}
	}

	function registerForm($x = null) {
		if ( !$this->post['register'] )
			return;

		if ( $this->registerterms ) {
			echo '<p><input type="checkbox" name="agree_terms" value="1" ';
			if ( $this->defaultterms['register'] )
				echo ' checked="checked"';
			echo ' />';
			echo html_entity_decode($this->registerterms).'</p>';
		}
	}

	function error($message) {
		global $wp_locale;

		while ( ob_get_level() )
			ob_end_clean();

		@nocache_headers();
		@header( 'Content-Type: text/html; charset=utf-8' );

		$message = "<p>$message</p>";

		if ( defined( 'WP_SITEURL' ) && '' != WP_SITEURL )
			$admin_dir = WP_SITEURL . '/wp-admin/';
		elseif ( function_exists( 'get_bloginfo' ) && '' != get_bloginfo( 'wpurl' ) )
			$admin_dir = get_bloginfo( 'wpurl' ) . '/wp-admin/';
		elseif ( strpos( $_SERVER['PHP_SELF'], 'wp-admin' ) !== false )
			$admin_dir = '';
		else
			$admin_dir = 'wp-admin/';

		if ( empty($title) ) {
			if ( function_exists( '__' ) )
				$title = __( 'WordPress &rsaquo; Error' );
			else
				$title = 'WordPress &rsaquo; Error';
		}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists( 'language_attributes' ) ) language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $title ?></title>
	<link rel="stylesheet" href="<?php echo $admin_dir; ?>css/install.css" type="text/css" />
<?php
if ( ( $wp_locale ) && ( 'rtl' == $wp_locale->text_direction ) ) : ?>
	<link rel="stylesheet" href="<?php echo $admin_dir; ?>css/install-rtl.css" type="text/css" />
<?php endif; ?>
</head>
<body id="error-page">
	<?php echo $message; ?>
</body>
</html>
<?php
		exit();
	}

	function checkRegisterTerms($x = null) {
		if ( $this->showterms['register'] && $_REQUEST['agree_terms'] != '1' ) {
			$this->error( 'Error: you must agree to the terms.' );
		}
		return $x;
	}

	function checkCommentTerms($x = null) {
		if ( $this->showterms['comment'] && $_REQUEST['agree_terms'] != '1' ) {
			$this->error( 'Error: you must agree to the terms.' );
		}
		return $x;
	}

	function install()
	{
		if ( $this->installed )
			return;

		global $wpdb;
		$wpdb->query("CREATE TABLE {$wpdb->prefix}aweber(
`email` VARCHAR( 255 ) NOT NULL ,
`when` DATETIME NOT NULL ,
PRIMARY KEY ( `email` )
)");

		add_option('wpaweber_version', $this->version);
		add_option('wpaweber_formid', '');
		add_option('wpaweber_unit', '');
		add_option('wpaweber_registerterms', '');
		add_option('wpaweber_commentterms', '');
		add_option('wpaweber_showterms', '');
		add_option('wpaweber_defaultterms', '');
		add_option('wpaweber_actions', 'register,comment');
		$this->installed = true;
	}

	function uninstall()
	{
		// Delete options
		delete_option('wpaweber_version');
		/*delete_option('wpaweber_formid');
		delete_option('wpaweber_unit');
		delete_option('wpaweber_actions');*/
	}

	function adminMenu()
	{
		add_submenu_page('options-general.php', 'Aweber', 'Aweber', 10, 'wpaweber', array(&$this, 'admin'));
	}

	function admin()
	{
?>
<div class="wrap">

<h2>Aweber Options</h2>


<p>Please put your name and email below to subscribe to our mailing list so you may receive udpates to this plugin as well as new feature releases:</p>

<!-- GetResponse subscription form | start -->

<form action="http://www.getresponse.com/cgi-bin/add.cgi" method="post" id="GRSubscribeForm" accept-charset="UTF-8">
<fieldset>
<table>


<tr>
<td>
<label for="GRCategory2">Your Name</label>:
</td>
<td><input type="text" name="category2" size="14" id="GRCategory2" /></td>
</tr><tr>
<td><label for="GRCategory3">Your E-Mail</label>:</td>
<td><input type="text" name="category3" size="14" id="GRCategory3" /></td>
</tr>

</table>
<input type="submit" value="Subscribe to Mailing List" />
</fieldset>
<input type="hidden" name="category1" value="aweberplugin" />
<input type="hidden" name="confirmation" value="http://www.imgodfather.com/forums/"/>
<input type="hidden" name="error_page" value="http://www.imgodfather.com/forums/"/>
<input type="hidden" name="ref" value="000" />
<input type="hidden" name="getpostdata" value="get" />
</form>
<style>
<!--
/* form box */
#GRSubscribeForm fieldset {
width: 260px;
border: 0;
}

/* comment about GetResponse */
#GRSubscribeForm p {
font-size: x-small;
}

/* table used to position form elements */
#GRSubscribeForm table {
border: 0;
}

-->
</style>
<!-- GetResponse subscription form | end -->
<hr>


<form method="post">
<?php
		if ( $_REQUEST['save'] ) {
			// check_admin_referer('forcefetch-campaign_'.$cid);
			$this->formid = $_REQUEST['formid'];
			$this->unit = $_REQUEST['unit'];
			$this->registerterms = htmlspecialchars($_REQUEST['registerterms']);
			$this->commentterms = htmlspecialchars($_REQUEST['commentterms']);
			$actions = array();
			foreach ( $this->post as $act => $enabled ) {
				$this->post[$act] = ($_REQUEST['post'][$act] == '1');
				if ( $this->post[$act] )
					$actions[] = $act;
			}
			$this->showterms = array();
			if ( $_REQUEST['showterms'] )
				foreach ( $_REQUEST['showterms'] as $k => $v )
					if ( $v == '1' )
						$this->showterms[$k] = true;
			$this->defaultterms = array();
			if ( $_REQUEST['defaultterms'] )
				foreach ( $_REQUEST['defaultterms'] as $k => $v )
					if ( $v == '1' )
						$this->defaultterms[$k] = true;

			update_option('wpaweber_formid', $this->formid);
			update_option('wpaweber_unit', $this->unit);
			update_option('wpaweber_actions', implode(',', $actions));
			update_option('wpaweber_registerterms', $this->registerterms);
			update_option('wpaweber_commentterms', $this->commentterms);
			update_option('wpaweber_showterms', implode(',', array_keys($this->showterms)));
			update_option('wpaweber_defaultterms', implode(',', array_keys($this->defaultterms)));

			if ( !empty($_POST ) ) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
<?php
			endif;
		}
		wp_nonce_field('awebersave');
?>

<p><b>Web Form Id:</b> <input type="text" name="formid" value="<?= $this->formid ?>" /><br />
<b>Unit:</b> <input type="text" name="unit" value="<?= $this->unit ?>" /></p>
<p><input type="checkbox" name="post[register]" value="1"<?php if ( $this->post['register'] ) { ?> checked="checked"<?php } ?> /> Subscribe on registration<br />
<input type="checkbox" name="post[comment]" value="1"<?php if ( $this->post['comment'] ) { ?> checked="checked"<?php } ?> /> Subscribe when commenting</p>
<p>Registration terms of use:<br />
<textarea name="registerterms" cols="60" rows="10"><?= $this->registerterms ?></textarea><br />
<input type="checkbox" name="showterms[register]" value="1" <?php if ( $this->showterms['register'] ) { ?> checked="checked"<?php } ?> /> Require when registering<br />
<input type="checkbox" name="defaultterms[register]" value="1" <?php if ( $this->defaultterms['register'] ) { ?> checked="checked"<?php } ?> /> Checked initially</p>
<p>Commenting terms of use:<br />
<textarea name="commentterms" cols="60" rows="10"><?= $this->commentterms ?></textarea><br />
<input type="checkbox" name="showterms[comment]" value="1" <?php if ( $this->showterms['comment'] ) { ?> checked="checked"<?php } ?> /> Require when commenting<br />
<input type="checkbox" name="defaultterms[comment]" value="1" <?php if ( $this->defaultterms['comment'] ) { ?> checked="checked"<?php } ?> /> Checked initially</p>
<p class="submit"><input type="submit" name="save" value="Save Options &raquo;" /></p>
</form>
</div>


<?php
	}

	function register($id) {
		global $wpdb;

		if ( !$this->post['register'] )
			return;

		// get user info
		$id = intval($id);
		$user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID=$id");

		// check if this email was submitted to aweber already
		if ( !$this->ExistingEmail($user->user_email) ) {
			// update password
			$user_pass = $this->generatePassword();
			$up = $this->hashPassword($user_pass);
			$wpdb->query("UPDATE {$wpdb->users} SET user_pass='$up' WHERE ID=$id");
			// send notifications
			//wp_new_user_notification($id, $user_pass);	// use this by itself if using pluggable.php function (sends email from wordpress@domain.com)
			wp_new_user_notification($id, ''); // this notifies admin only (no password is passed)
			$EMAIL_HEADER = "From: ".get_option('blogname')." <".get_option('admin_email').">\r\n";
			$EMAIL_HEADER .= "MIME-Version: 1.0\r\n";
			$EMAIL_HEADER .= "X-Priority: 1\r\n";
			$EMAIL_HEADER .= "X-MSmail-Priority: High\r\n";
			$SUBJECT="[".get_option('blogname')."] Your username and password";
			$MESSAGE="Username: ".$user->user_login."\r\nPassword: ".$user_pass."\r\n".site_url("wp-login.php", 'login') . "\r\n";
			mail($user->user_email,$SUBJECT,$MESSAGE,$EMAIL_HEADER);

			// do the form
			$this->AweberForm($user->user_email, $user->user_login, get_option('siteurl')."/wp-login.php?checkemail=registered");
		}
	}

	function generatePassword() {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$length = 7;
		$password = '';
		for ( $i = 0; $i < $length; $i++ )
			$password .= substr($chars, mt_rand(0, 61), 1);
		return $password;
	}

	function hashPassword($password) {
		if ( !class_exists('PasswordHash') ) {
			if ( file_exists(ABSPATH . 'wp-includes/class-phpass.php') )
				require_once( ABSPATH . 'wp-includes/class-phpass.php');
			else
				require_once( ABSPATH . 'wp-content/plugins/wpaweber/class-phpass.php');
		}
		// By default, use the portable hash from phpass
		$wp_hasher = new PasswordHash(8, TRUE);

		return $wp_hasher->HashPassword($password);
	}

	function commentStatus($id, $status = '') {
		global $wpdb;

		if ( !$this->post['comment'] )
			return;

		if ( $status != 'approve' )
			return;

		// get submitter
		$id = intval($id);
		$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID=$id");

		if ( $comment->user_id )
			return;

		$this->AweberRegister($comment->comment_author_email, $comment->comment_author);
	}

	function commentPost($id, $status) {
		global $wpdb;

		if ( !$this->post['comment'] )
			return;

		//if ( $status != 1 )
			//return;

		// get submitter
		$id = intval($id);
		$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID=$id");

		if ( $comment->user_id )
			return;

		// this is now processed by the comment redirect filter
		//$this->AweberRegister($comment->comment_author_email, $comment->comment_author);
		$this->email = $comment->comment_author_email;
		$this->name = $comment->comment_author;
	}

	function commentRedirect($location) {
		if ( !$this->email )
			return $location;

		$this->AweberForm($this->email, $this->name, $location);
		return $location;
	}

	function ExistingEmail($email) {
		global $wpdb;

		$qe = $wpdb->escape(strtolower($email));
		$prev = $wpdb->get_row("SELECT * FROM $this->table WHERE LOWER(email)='$qe'");
		if ( $prev )
			return true;
		return false;
	}

	function AweberForm($email, $name, $location) {
		global $wpdb;

		$qe = $wpdb->escape(strtolower($email));
		if ( $this->ExistingEmail($email) )
			return;

		$r = $wpdb->query("INSERT INTO $this->table (`email`,`when`) VALUES('$qe', NOW())");

?>
<html>
<head>
<title></title>
</head>
<body>
<form method="post" action="http://www.aweber.com/scripts/addlead.pl" name="awebersub" id="awebersub">
<input type="hidden" name="meta_web_form_id" value="<?= $this->formid ?>">
<input type="hidden" name="meta_split_id" value="">
<input type="hidden" name="unit" value="<?= $this->unit ?>">
<input type="hidden" name="redirect" value="<?= htmlspecialchars($location) ?>">
<input type="hidden" name="meta_redirect_onlist" value="<?= htmlspecialchars($location) ?>">
<input type="hidden" name="meta_adtracking" value="">
<input type="hidden" name="meta_message" value="1">
<input type="hidden" name="meta_required" value="from">
<input type="hidden" name="meta_forward_vars" value="0">
<input type="hidden" name="from" value="<?= htmlspecialchars($email) ?>">
<input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
<input type="submit" name="submitform" value="Continue" id="continue">
</form>
<span style="display: none;" id="redir">Redirecting...</span>
<script type="text/javascript" defer="defer">
document.forms['awebersub'].submit();
document.getElementById('continue').enabled = false;
document.getElementById('continue').style.display = 'none';
document.getElementById('redir').style.display = '';
</script>
</body>
</html>
<?php
		exit();
	}

	function AweberRegister($email, $name) {
		global $wpdb;

		$qe = $wpdb->escape(strtolower($email));
		$prev = $wpdb->get_row("SELECT * FROM $this->table WHERE LOWER(email)='$qe'");
		if ( $prev )
			return;

		$r = $wpdb->query("INSERT INTO $this->table (`email`,`when`) VALUES('$qe', NOW())");

		$params = array(
			"meta_web_form_id" => $this->formid,
			"meta_split_id" => "",
			"unit" => $this->unit,
			"redirect" => "http://www.aweber.com/form/thankyou_vo.html",
			"meta_redirect_onlist" => "",
			"meta_adtracking" => "",
			"meta_message" => "1",
			"meta_required" => "from",
			"meta_forward_vars" => "0",
			"from" => $email,
			"name" => $name,
			"submit" => "Submit"
		);

		$r = $this->_post('http://www.aweber.com/scripts/addlead.pl', $params);
	}


	function _post($url, $fields) {
		return $this->_request(true, $url, $fields);
	}

	function _request($post, $url, $fields) {
		$postfields = array();
		if ( count($fields) )
			foreach ( $fields as $i => $f )
				$postfields[] = urlencode($i) . '=' . urlencode($f);
		$fields = implode('&', $postfields);

		return $this->_http($post ? 'POST' : 'GET', $url, $fields);

		$ch = curl_init($url);

		$ck = array();
		if ( count($this->cookies) )
			foreach ( $this->cookies as $n => $v )
				$ck[] = $n . '=' . $v;
		$headers = array(
			"Cookie: " . implode('; ', $ck),
			"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11", "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5", "Accept-Language: en-us,en;q=0.5", "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7", "Keep-Alive: 300", "Connection: keep-alive",
		);

		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		if ( $post ) {
			curl_setopt($ch, CURLOPT_POST, true);

			$postfields = array();
			if ( count($fields) )
				foreach ( $fields as $i => $f )
					$postfields[] = urlencode($i) . '=' . urlencode($f);
			$fields = implode('&', $postfields);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

			$headers[] = "Content-Type: application/x-www-form-urlencoded";
			$headers[] = "Content-Length: " . strlen($fields);
		}
		curl_setopt($ch, CURLOPT_HEADER, $this->headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if ( isset($this->last) && $this->last )
			curl_setopt($ch, CURLOPT_REFERER, $this->last);

		$res = curl_exec($ch);
		if ( !is_string($res) )
			$this->_log($res, true);
		curl_close($ch);

		if ( $res )
			foreach ( explode("\n", $res) as $r )
				if ( preg_match('~Set-Cookie:\s+([^=]+)=([^;]*)~i', $r, $subs) )
					$this->cookies[$subs[1]] = $subs[2];

		return $res;
	}

	function _http($method, $url, $data = null) {
		preg_match('~http://([^/]+)(/.*)~', $url, $subs);
		$host = $subs[1];
		$uri = $subs[2];

		$header .= "$method $uri HTTP/1.1\r\n";
		$header .= "Host: $host\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($data) . "\r\n\r\n";
		$fp = fsockopen ($host, 80, $errno, $errstr, 30);

		if ( $fp ) {
			fputs($fp, $header . $data);
			$result = '';
			while ( !feof($fp) )
				$result .= fgets($fp, 4096);
		}

		fclose($fp);
		return $result;
	}
}
?>