<?php
/*
Plugin Name: Dock Gallery FX
Plugin URI: http://www.flashxml.net/dock-gallery.html
Description: An original "Dock Gallery". Completely XML customizable without any Flash knowledge. And it's free!
Version: 0.8.0
Author: FlashXML.net
Author URI: http://www.flashxml.net/
License: GPL2
*/

/* start global parameters */
	$fxdockgallery_params = array(
		'count'	=> 0, // number of Dock Gallery FX embeds
		'wmode_values' => array(
			'allowed' => array('transparent', 'window'),
			'default' => 'transparent',
		),
		'regexp_match_keys' => array(
			'width' => 1,
			'height' => 2,
			'settings' => 4,
			'wmode' => 6,
			'alternative_text' => 7,
		),
	);
/* end global parameters */

/* start client side functions */
	function fxdockgallery_get_embed_code($fxdockgallery_attributes) {
		global $fxdockgallery_params;
		$fxdockgallery_params['count']++;

		$fxdockgallery_wp_content_url = str_replace(array("http://{$_SERVER['HTTP_HOST']}", "https://{$_SERVER['HTTP_HOST']}"), array('', ''), WP_CONTENT_URL);

		$width = (int)$fxdockgallery_attributes[1];
		$height = (int)$fxdockgallery_attributes[2];

		if ($width == 0 || $height == 0) {
			return '<!-- invalid Dock Gallery FX width and / or height -->';
		}

		$plugin_dir = get_option('fxdockgallery_path');
		if ($plugin_dir === false) {
			$plugin_dir = 'flashxml/dock-gallery-fx';
		}
		$plugin_dir = trim($plugin_dir, '/');

		if (empty($fxdockgallery_attributes[$fxdockgallery_params['regexp_match_keys']['wmode']])) {
			$wmode = get_option('fxdockgallery_wmode');
			if (empty($wmode)) {
				$wmode = $fxdockgallery_params['wmode_values']['default'];
			}
		} else {
			$wmode = in_array($fxdockgallery_attributes[$fxdockgallery_params['regexp_match_keys']['wmode']], $fxdockgallery_params['wmode_values']['allowed']) ? $fxdockgallery_attributes[$fxdockgallery_params['regexp_match_keys']['wmode']] : $fxdockgallery_params['wmode_values']['default'];
		}

		$swf_embed = array(
			'width' => $width,
			'height' => $height,
			'text' => isset($fxdockgallery_attributes[$fxdockgallery_params['regexp_match_keys']['alternative_text']]) ? trim($fxdockgallery_attributes[$fxdockgallery_params['regexp_match_keys']['alternative_text']]) : '',
			'gallery_path' => "{$fxdockgallery_wp_content_url}/{$plugin_dir}/",
			'swf_name' => 'DockGalleryFX.swf',
			'wmode' => $wmode,
		);
		$swf_embed['swf_path'] = $swf_embed['gallery_path'].$swf_embed['swf_name'];

		$settings_file_name = !empty($fxdockgallery_attributes[$fxdockgallery_params['regexp_match_keys']['settings']]) ? $fxdockgallery_attributes[$fxdockgallery_params['regexp_match_keys']['settings']] : 'settings.xml';

		if (!is_feed()) {
			$embed_code = '<div id="flashxmldockgallery'.$fxdockgallery_params['count'].'">'.$swf_embed['text'].'</div>';
			$embed_code .= '<script type="text/javascript">';
			$embed_code .= "swfobject.embedSWF('{$swf_embed['swf_path']}', 'flashxmldockgallery{$fxdockgallery_params['count']}', '{$swf_embed['width']}', '{$swf_embed['height']}', '9.0.0.0', '', { folderPath: '{$swf_embed['gallery_path']}'".($settings_file_name != 'settings.xml' ? ", settingsXML: '{$settings_file_name}', navigationSettingsXML: 'DockMenuFX/{$settings_file_name}', gallerySettingsXML: 'holder/{$settings_file_name}'" : '')." }, { scale: 'noscale', salign: 'tl', wmode: '{$swf_embed['wmode']}', allowScriptAccess: 'sameDomain', allowFullScreen: true }, {});";
			$embed_code.= '</script>';
		} else {
			$embed_code = '<object width="'.$swf_embed['width'].'" height="'.$swf_embed['height'].'">';
			$embed_code .= '<param name="movie" value="'.$swf_embed['swf_path'].'"></param>';
			$embed_code .= '<param name="scale" value="noscale"></param>';
			$embed_code .= '<param name="salign" value="tl"></param>';
			$embed_code .= '<param name="wmode" value="'.$swf_embed['wmode'].'"></param>';
			$embed_code .= '<param name="allowScriptAccess" value="sameDomain"></param>';
			$embed_code .= '<param name="allowFullScreen" value="true"></param>';
			$embed_code .= '<param name="sameDomain" value="true"></param>';
			$embed_code .= '<param name="flashvars" value="folderPath='.$swf_embed['gallery_path'].($settings_file_name != 'settings.xml' ? '&settingsXML='.$settings_file_name.'&navigationSettingsXML='.$settings_file_name.'&gallerySettingsXML='.$settings_file_name : '').'"></param>';
			$embed_code .= '<embed type="application/x-shockwave-flash" width="'.$swf_embed['width'].'" height="'.$swf_embed['height'].'" src="'.$swf_embed['swf_path'].'" scale="noscale" salign="tl" wmode="window" allowScriptAccess="sameDomain" allowFullScreen="true" flashvars="folderPath='.$swf_embed['gallery_path'].($settings_file_name != 'settings.xml' ? '&settingsXML='.$settings_file_name.'&navigationSettingsXML=DockMenuFX/'.$settings_file_name.'&gallerySettingsXML=holder/'.$settings_file_name : '').'"';
			$embed_code .= '></embed>';
			$embed_code .= '</object>';
		}

		return $embed_code;
	}

	function fxdockgallery_filter_content($content) {
		return preg_replace_callback('|\[dock-gallery-fx\s+width\s*=\s*"(\d+)"\s+height\s*=\s*"(\d+)"\s*(settings="([^"]+)")?\s*(wmode="([a-z]+)")?\s*\](.*)\[/dock-gallery-fx\]|i', 'fxdockgallery_get_embed_code', $content);
	}

	function fxdockgallery_echo_embed_code($width, $height, $div_text = '', $settings_xml = '', $wmode = 'transparent') {
		global $fxdockgallery_params;

		echo fxdockgallery_get_embed_code(array($fxdockgallery_params['regexp_match_keys']['width'] => $width, $fxdockgallery_params['regexp_match_keys']['height'] => $height, $fxdockgallery_params['regexp_match_keys']['settings'] => $settings_xml, $fxdockgallery_params['regexp_match_keys']['wmode'] => $wmode, $fxdockgallery_params['regexp_match_keys']['alternative_text'] => $div_text));
	}

	function fxdockgallery_load_swfobject_lib() {
		wp_enqueue_script('swfobject');
	}
/* end client side functions */

/* start admin section functions */
	function fxdockgallery_admin_menu() {
		add_options_page('Dock Gallery FX Options', 'Dock Gallery FX', 'manage_options', 'fxdockgallery', 'fxdockgallery_admin_options');
	}

	function fxdockgallery_admin_options() {
	  if (!current_user_can('manage_options'))  {
				wp_die(__('You do not have sufficient permissions to access this page.'));
		}

	  global $fxdockgallery_params;

	  $fxdockgallery_default_path = get_option('fxdockgallery_path');
	  if ($fxdockgallery_default_path === false) {
	  	$fxdockgallery_default_path = 'flashxml/dock-gallery-fx';
	  }

 	  $fxdockgallery_default_wmode = get_option('fxdockgallery_wmode');
	  if ($fxdockgallery_default_wmode === false) {
	  	$fxdockgallery_default_wmode = $fxdockgallery_params['wmode_values']['default'];
	  }
?>
<div class="wrap">
	<h2>Dock Gallery FX</h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row" style="width: 40em;">SWF and assets path is <?php echo basename(WP_CONTENT_DIR); ?>/</th>
				<td><input type="text" style="width: 25em;" name="fxdockgallery_path" value="<?php echo $fxdockgallery_default_path; ?>" /></td>
			</tr>
			<tr>
				<th scope="row" style="width: 40em;">SWF wmode parameter</th>
				<td>
					<select style="width: 27.5em;" name="fxdockgallery_wmode">
<?php
		foreach ($fxdockgallery_params['wmode_values']['allowed'] as $fxdockgallery_allowed_wmode_value) {
?>
						<option value="<?php echo $fxdockgallery_allowed_wmode_value; ?>"<?php echo $fxdockgallery_allowed_wmode_value == $fxdockgallery_default_wmode ? ' selected="selected"' : ''; ?>><?php echo $fxdockgallery_allowed_wmode_value; ?></option>
<?php
		}
?>
					</select>
				</td>
			</tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="fxdockgallery_path,fxdockgallery_wmode" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
<?php
	}
/* end admin section functions */

/* start hooks */
	add_filter('the_content', 'fxdockgallery_filter_content');
	add_action('init', 'fxdockgallery_load_swfobject_lib');
	add_action('admin_menu', 'fxdockgallery_admin_menu');
/* end hooks */

?>