<?php
/*
 * Plugin Name:   SEO Post Link
 * Version:       1.0.3
 * Plugin URI:    http://www.maxblogpress.com/plugins/spl/
 * Description:   Automatically make your post link short and SEO friendly by removing unnecessary words from your post slug. Adjust your settings <a href="options-general.php?page=seo-post-link/seo-post-link.php">here</a>.
 * Author:        MaxBlogPress
 * Author URI:    http://www.maxblogpress.com
 *
 *
 * License: Copyright (c) 2008 Pawan Agrawal. All rights reserved.
 * 
 * This plugin uses a commercial script library.
 * 
 * Please refer to "license.txt" file located at "seo-post-link-lib/"
 * for copyright notice and end user license agreement.
 * 
 */ 
 
$spl_path 		= preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$spl_path     	= str_replace('\\','/',$spl_path);
$site_url     	= get_bloginfo('wpurl');
$site_url     	= (strpos($site_url,'http://') === false)  ? get_bloginfo('siteurl') : $site_url;
$spl_relpath  	= str_replace('\\','/',dirname(__FILE__));
$spl_fullpath 	= $site_url.'/wp-content/plugins/'.substr($spl_path,0,strrpos($spl_path,'/')).'/';
$spl_abspath 	= str_replace("\\","/",ABSPATH);  
define('SPL_NAME', 'SEO Post Link');	// Name of the Plugin
define('SPL_VERSION', '1.0.3');			// Current version of the Plugin
define('SPL_PATH', $spl_path);
define('SPL_FULLPATH', $spl_fullpath);
require_once($spl_relpath.'/seo-post-link-lib/include/seo-post-link.cls.php');

/**
 * ShortPostLinks - SEO Post Link Class
 * Holds all the necessary functions and variables
 */
class ShortPostLinksPlugin extends ShortPostLinks
{
    /**
     * SEO Post Link plugin path
     * @var string
     */
	var $spl_path;
	
    /**
     * SEO Post Link options. Holds various settings for short post links.
     * @var string
     */
	var $spl_options;
	
    /**
     * SEO Post Link omitted words file name.
     * @var string
     */
	var $spl_omitwords_file = 'omitted-words.txt';
	
	/**
     * Holds the default settings values
	 * These values will be set while activating the plugin
     * @var array
     */
	var	$default_settings = array(
					'spl_max_length' => 30, 'spl_min_word_length' => 4
					);

	/**
	 * Constructor. Adds SEO Post Link plugin's actions/filters.
	 * @access public
	 */
	function ShortPostLinksPlugin() { 
		$this->img_how      = '<img src="'.$spl_fullpath.'images/how.gif" border="0" align="absmiddle">';
		$this->img_comment  = '<img src="'.$spl_fullpath.'images/comment.gif" border="0" align="absmiddle">';
	    
		add_action('activate_'.SPL_PATH, array(&$this, 'splActivate'));
		add_action('admin_menu', array(&$this, 'splAddMenu'));
		$this->spl_activate = get_option('spl_activate');
		if ( $this->spl_activate == 2 ) {
			add_filter('name_save_pre', array(&$this, 'splTrimSlug')); 
		}
		if( !$this->spl_options = get_option('spl_options') ) {
			$this->spl_options  = $this->default_settings;
		}
		if( !$this->spl_omitted_words = get_option('spl_omitted_words') ) {
			$this->spl_omitted_words  = array();
		}
	}
	
	/**
	 * Called when plugin is activated. Adds option value to the options table.
	 * @access public
	 */
	function splActivate() {
		$this->__splActivate();		
		return true;
	}
	
	/**
	 * Creates a clean and short post slug
	 * @param string $slug
	 * @access public
	 */
	function splTrimSlug($slug) {
		global $wpdb;
		
		// If slug already exists or is manually entered 
		if ( !empty($slug) ) {
			return $slug;
		}
		
		$spl_title = trim($_POST['post_title']);
		if ( (strlen($spl_title) > $this->spl_options['spl_max_length']) || $this->spl_options['spl_force_removal'] ) {
			$spl_title_new = $this->__splTrimSlug($spl_title);
			return sanitize_title(trim($spl_title_new));
		} else {
			return '';
		}
	}
	
	/**
	 * Adds "SEO Post Link" link to admin Options menu
	 * @access public 
	 */
	function splAddMenu() {
		add_options_page('SEO Post Link', 'SEO Post Link', 'manage_options', SPL_PATH, array(&$this, 'splOptionsPg'));
	}
	
	/**
	 * Page Header
	 */
	function splHeader() {
		if ( !isset($_GET['dnl']) ) {	
			$spl_version_chk = $this->splRecheckData();
			if ( ($spl_version_chk == '') || strtotime(date('Y-m-d H:i:s')) > (strtotime($spl_version_chk['last_checked_on']) + $spl_version_chk['recheck_interval']*60*60) ) {
				$update_arr = $this->splExtractUpdateData();
				if ( count($update_arr) > 0 ) {
					$latest_version   = $update_arr[0];
					$recheck_interval = $update_arr[1];
					$download_url     = $update_arr[2];
					$msg_in_plugin    = $update_arr[3];
					$msg_in_plugin    = $update_arr[4];
					$upgrade_url      = $update_arr[5];
					if( SPL_VERSION < $latest_version ) {
						$spl_version_check = array('recheck_interval' => $recheck_interval, 'last_checked_on' => date('Y-m-d H:i:s'));
						$this->splRecheckData($spl_version_check);
						$msg_in_plugin = str_replace("%latest-version%", $latest_version, $msg_in_plugin);
						$msg_in_plugin = str_replace("%plugin-name%", SPL_NAME, $msg_in_plugin);
						$msg_in_plugin = str_replace("%upgrade-url%", $upgrade_url, $msg_in_plugin);
						$msg_in_plugin = '<div style="border-bottom:1px solid #CCCCCC;background-color:#FFFEEB;padding:6px;font-size:11px;text-align:center">'.$msg_in_plugin.'</div>';
					} else {
						$msg_in_plugin = '';
					}
				}
			}
		}
		echo '<div style="font-family:arial; font-size:14px;font-weight:bold; padding:0px 0px 0px 6px; width:98%; border:1px solid #C9DCEC;background-color:#C3D9FF;margin-top:6px;">';
		echo '<h2>'.SPL_NAME.' '.SPL_VERSION.'</h2>';
		echo '</div>';
		
		if ( trim($msg_in_plugin) != '' && !isset($_GET['dnl']) ) echo $msg_in_plugin;
		#echo '<br /><strong>'.$this->img_how.' <a href="http://www.maxblogpress.com/plugins/spl/spl-use/" target="_blank">How to use it</a>&nbsp;&nbsp;&nbsp;'; 
        #echo $this->img_comment.' <a href="http://community.maxblogpress.com" target="_blank">Comments and Suggestions</a></strong><br /><br />';
	}
	
	/**
	 * Page Footer
	 */
	function splFooter() {
		echo '<p style="text-align:center;margin-top:3em;"><strong>'.SPL_NAME.' '.SPL_VERSION.' by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>';
	}
	
	/**
	 * "SEO Post Link" Options page
	 * @access public 
	 */
	function splOptionsPg() {
		global $wpdb;
		$msg = '';

		$form_1 = 'spl_reg_form_1';
		$form_2 = 'spl_reg_form_2';
		// Activate the plugin if email already on list
		if ( trim($_GET['mbp_onlist']) == 1 ) { 
			$this->spl_activate = 2;
			update_option('spl_activate', $this->spl_activate);
			$msg = 'Thank you for registering the plugin. It has been activated'; 
		} 
		// If registration form is successfully submitted
		if ( ((trim($_GET['submit']) != '' && trim($_GET['from']) != '') || trim($_GET['submit_again']) != '') && $this->spl_activate != 2 ) { 
			update_option('spl_name', $_GET['name']);
			update_option('spl_email', $_GET['from']);
			$this->spl_activate = 1;
			update_option('spl_activate', $this->spl_activate);
		}
		if ( intval($this->spl_activate) == 0 ) { // First step of plugin registration
			$this->splRegister_1($form_1);
		} else if ( intval($this->spl_activate) == 1 ) { // Second step of plugin registration
			$name  = get_option('spl_name');
			$email = get_option('spl_email');
			$this->splRegister_2($form_2,$name,$email);
		} else if ( intval($this->spl_activate) == 2 ) { // Options page
			if ( $_GET['action'] == 'upgrade' ) {
				$this->splUpgradePlugin();
				exit;
			}
			$this->__splOptionsPg();
		}
	}
	
	/**
	 * Gets recheck data fro displaying auto upgrade information
	 */
	function splRecheckData($data='') {
		if ( $data != '' ) {
			update_option('spl_version_check',$data);
		} else {
			$version_chk = get_option('spl_version_check');
			return $version_chk;
		}
	}
	
	/**
	 * Extracts plugin update data
	 */
	function splExtractUpdateData() {
		$arr = array();
		$version_chk_file = "http://www.maxblogpress.com/plugin-updates/seo-post-link.php?v=".SPL_VERSION;
		$content = wp_remote_fopen($version_chk_file);
		if ( $content ) {
			$content          = nl2br($content);
			$content_arr      = explode('<br />', $content);
			$latest_version   = trim(trim(strstr($content_arr[0],'~'),'~'));
			$recheck_interval = trim(trim(strstr($content_arr[1],'~'),'~'));
			$download_url     = trim(trim(strstr($content_arr[2],'~'),'~'));
			$msg_plugin_mgmt  = trim(trim(strstr($content_arr[3],'~'),'~'));
			$msg_in_plugin    = trim(trim(strstr($content_arr[4],'~'),'~'));
			$upgrade_url      = $site_url.'/wp-admin/options-general.php?page='.SPL_PATH.'&action=upgrade&dnl='.$download_url;
			$arr = array($latest_version, $recheck_interval, $download_url, $msg_plugin_mgmt, $msg_in_plugin, $upgrade_url);
		}
		return $arr;
	}
	
	/**
	 * Interface for upgrading plugin
	 */
	function splUpgradePlugin() {
		global $wp_version;
		$plugin = SPL_PATH;
		echo '<div class="wrap">';
		$this->splHeader();
		echo '<h3>Upgrade Plugin &raquo;</h3>';
		if ( $wp_version >= 2.5 ) {
			$res = $this->splDoPluginUpgrade($plugin);
		} else {
			echo '&raquo; Wordpress 2.5 or higher required for automatic upgrade.<br><br>';
		}
		if ( $res == false ) echo '&raquo; Plugin couldn\'t be upgraded.<br><br>';
		echo '<br><strong><a href="'.$site_url.'/wp-admin/plugins.php">Go back to plugins page</a> | <a href="'.$site_url.'/wp-admin/options-general.php?page='.SPL_PATH.'">'.SPL_NAME.' home page</a></strong>';
		$this->splFooter();
		echo '</div>';
		include('admin-footer.php');
	}
	
	/**
	 * Carries out plugin upgrade
	 */
	function splDoPluginUpgrade($plugin) {
		set_time_limit(300);
		global $wp_filesystem;
		$debug = 0;
		$was_activated = is_plugin_active($plugin); // Check current status of the plugin to retain the same after the upgrade

		// Is a filesystem accessor setup?
		if ( ! $wp_filesystem || !is_object($wp_filesystem) ) {
			WP_Filesystem();
		}
		if ( ! is_object($wp_filesystem) ) {
			echo '&raquo; Could not access filesystem.<br /><br />';
			return false;
		}
		if ( $wp_filesystem->errors->get_error_code() ) {
			echo '&raquo; Filesystem error '.$wp_filesystem->errors.'<br /><br />';
			return false;
		}
		
		if ( $debug ) echo '> File System Okay.<br /><br />';
		
		// Get the URL to the zip file
		$package = $_GET['dnl'];
		if ( empty($package) ) {
			echo '&raquo; Upgrade package not available.<br /><br />';
			return false;
		}
		// Download the package
		$file = download_url($package);
		if ( is_wp_error($file) || $file == '' ) {
			echo '&raquo; Download failed. '.$file->get_error_message().'<br /><br />';
			return false;
		}
		$working_dir = $spl_abspath . 'wp-content/upgrade/' . basename($plugin, '.php');
		
		if ( $debug ) echo '> Working Directory = '.$working_dir.'<br /><br />';
		
		// Unzip package to working directory
		$result = $this->splUnzipFile($file, $working_dir);
		if ( is_wp_error($result) ) {
			unlink($file);
			$wp_filesystem->delete($working_dir, true);
			echo '&raquo; Couldn\'t unzip package to working directory. Make sure that "/wp-content/upgrade/" folder has write permission (CHMOD 755).<br /><br />';
			return $result;
		}
		
		if ( $debug ) echo '> Unzip package to working directory successful<br /><br />';
		
		// Once extracted, delete the package
		unlink($file);
		if ( is_plugin_active($plugin) ) {
			deactivate_plugins($plugin, true); //Deactivate the plugin silently, Prevent deactivation hooks from running.
		}
		
		// Remove the old version of the plugin
		$plugin_dir = dirname($spl_abspath . PLUGINDIR . "/$plugin");
		$plugin_dir = trailingslashit($plugin_dir);
		// If plugin is in its own directory, recursively delete the directory.
		if ( strpos($plugin, '/') && $plugin_dir != $base . PLUGINDIR . '/' ) {
			$deleted = $wp_filesystem->delete($plugin_dir, true);
		} else {

			$deleted = $wp_filesystem->delete($base . PLUGINDIR . "/$plugin");
		}
		if ( !$deleted ) {
			$wp_filesystem->delete($working_dir, true);
			echo '&raquo; Could not remove the old plugin. Make sure that "/wp-content/plugins/" folder has write permission (CHMOD 755).<br /><br />';
			return false;
		}
		
		if ( $debug ) echo '> Old version of the plugin removed successfully.<br /><br />';

		// Copy new version of plugin into place
		if ( !$this->splCopyDir($working_dir, $spl_abspath . PLUGINDIR) ) {
			echo '&raquo; Installation failed. Make sure that "/wp-content/plugins/" folder has write permission (CHMOD 755)<br /><br />';
			return false;
		}
		//Get a list of the directories in the working directory before we delete it, we need to know the new folder for the plugin
		$filelist = array_keys( $wp_filesystem->dirlist($working_dir) );
		// Remove working directory
		$wp_filesystem->delete($working_dir, true);
		// if there is no files in the working dir
		if( empty($filelist) ) {
			echo '&raquo; Installation failed.<br /><br />';
			return false; 
		}
		$folder = $filelist[0];
		$plugin = get_plugins('/' . $folder);      // Pass it with a leading slash, search out the plugins in the folder, 
		$pluginfiles = array_keys($plugin);        // Assume the requested plugin is the first in the list
		$result = $folder . '/' . $pluginfiles[0]; // without a leading slash as WP requires
		
		if ( $debug ) echo '> Copy new version of plugin into place successfully.<br /><br />';
		
		if ( is_wp_error($result) ) {
			echo '&raquo; '.$result.'<br><br>';
			return false;
		} else {
			//Result is the new plugin file relative to PLUGINDIR
			echo '&raquo; Plugin upgraded successfully<br><br>';	
			if( $result && $was_activated ){
				echo '&raquo; Attempting reactivation of the plugin...<br><br>';	
				echo '<iframe style="display:none" src="' . wp_nonce_url('update.php?action=activate-plugin&plugin=' . $result, 'activate-plugin_' . $result) .'"></iframe>';
				sleep(15);
				echo '&raquo; Plugin reactivated successfully.<br><br>';	
			}
			return true;
		}
	}
	
	/**
	 * Copies directory from given source to destinaktion
	 */
	function splCopyDir($from, $to) {
		global $wp_filesystem;
		$dirlist = $wp_filesystem->dirlist($from);
		$from = trailingslashit($from);
		$to = trailingslashit($to);
		foreach ( (array) $dirlist as $filename => $fileinfo ) {
			if ( 'f' == $fileinfo['type'] ) {
				if ( ! $wp_filesystem->copy($from . $filename, $to . $filename, true) ) return false;
				$wp_filesystem->chmod($to . $filename, 0644);
			} elseif ( 'd' == $fileinfo['type'] ) {
				if ( !$wp_filesystem->mkdir($to . $filename, 0755) ) return false;
				if ( !$this->splCopyDir($from . $filename, $to . $filename) ) return false;
			}
		}
		return true;
	}
	
	/**
	 * Unzips the file to given directory
	 */
	function splUnzipFile($file, $to) {
		global $wp_filesystem;
		if ( ! $wp_filesystem || !is_object($wp_filesystem) )
			return new WP_Error('fs_unavailable', __('Could not access filesystem.'));
		$fs =& $wp_filesystem;
		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
		$archive = new PclZip($file);
		// Is the archive valid?
		if ( false == ($archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING)) )
			return new WP_Error('incompatible_archive', __('Incompatible archive'), $archive->errorInfo(true));
		if ( 0 == count($archive_files) )
			return new WP_Error('empty_archive', __('Empty archive'));
		$to = trailingslashit($to);
		$path = explode('/', $to);
		$tmppath = '';
		for ( $j = 0; $j < count($path) - 1; $j++ ) {
			$tmppath .= $path[$j] . '/';
			if ( ! $fs->is_dir($tmppath) )
				$fs->mkdir($tmppath, 0755);
		}
		foreach ($archive_files as $file) {
			$path = explode('/', $file['filename']);
			$tmppath = '';
			// Loop through each of the items and check that the folder exists.
			for ( $j = 0; $j < count($path) - 1; $j++ ) {
				$tmppath .= $path[$j] . '/';
				if ( ! $fs->is_dir($to . $tmppath) )
					if ( !$fs->mkdir($to . $tmppath, 0755) )
						return new WP_Error('mkdir_failed', __('Could not create directory'));
			}
			// We've made sure the folders are there, so let's extract the file now:
			if ( ! $file['folder'] )
				if ( !$fs->put_contents( $to . $file['filename'], $file['content']) )
					return new WP_Error('copy_failed', __('Could not copy file'));
				$fs->chmod($to . $file['filename'], 0755);
		}
		return true;
	}
} // Eof Class

$ShortPostLinksPlugin = new ShortPostLinksPlugin();
?>