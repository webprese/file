<?php
/*
Plugin Name: Tabbed Content FX
Plugin URI: http://www.flashxml.net/tabbed-content.html
Description: An advanced Tabbed Content. Fully XML customizable, without any Flash knowledge.
Version: 0.8.1
Author: FlashXML.net
Author URI: http://www.flashxml.net/
License: GPL2
*/

/* start global parameters */
	$fxtabbedcontent_params = array(
		'count'	=> 0, // number of Tabbed Content FX embeds
		'read_settings_from_url' => false, // true only when the settings XML file must be read via HTTP (is generated dynamically or is hosted on another domain)
		'wmode_values' => array(
			'allowed' => array('transparent', 'window'),
			'default' => 'transparent',
		),
		'regexp_match_keys' => array(
			'settings' => 2,
			'width' => 4,
			'height' => 6,
			'wmode' => 8,
			'alternative_text' => 9,
		),
	);
/* end global parameters */

/* start client side functions */
	function fxtabbedcontent_get_embed_code($fxtabbedcontent_attributes) {
		global $fxtabbedcontent_params;
		$fxtabbedcontent_params['count']++;

		$fxtabbedcontent_wp_content_url = str_replace(array("http://{$_SERVER['HTTP_HOST']}", "https://{$_SERVER['HTTP_HOST']}"), array('', ''), WP_CONTENT_URL);

		$plugin_dir = get_option('fxtabbedcontent_path');
		if ($plugin_dir === false) {
			$plugin_dir = 'flashxml/tabbed-content-fx';
		}
		$plugin_dir = trim($plugin_dir, '/');

		$settings_file_name = !empty($fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['settings']]) ? html_entity_decode(urldecode($fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['settings']])) : 'settings.xml';

		$settings_wp_content_prefix = $fxtabbedcontent_params['read_settings_from_url'] && (strtolower(ini_get('allow_url_fopen')) == 'on' || strtolower(ini_get('allow_url_fopen')) == '1') ? $fxtabbedcontent_wp_content_url : WP_CONTENT_DIR;
		$settings_path = "{$settings_wp_content_prefix}/{$plugin_dir}/{$settings_file_name}";

		$width = $height = 0;

		if (function_exists('simplexml_load_file') && ($settings_wp_content_prefix == $fxtabbedcontent_wp_content_url || $settings_wp_content_prefix == WP_CONTENT_DIR && file_exists($settings_path))) {
			$data = simplexml_load_file($settings_path);
			if ($data) {
				$width_attributes_array = $data->General_Properties->componentWidth->attributes();
				$width = !empty($width_attributes_array) ? (int)$width_attributes_array['value'] : 0;
				$height_attributes_array = $data->General_Properties->componentHeight->attributes();
				$height = !empty($height_attributes_array) ? (int)$height_attributes_array['value'] : 0;
			}
		}

		if (!($width > 0 && $height > 0)) {
			if ((int)$fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['width']] > 0 && (int)$fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['height']] > 0) {
				$width = (int)$fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['width']];
				$height = (int)$fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['height']];
			} else {
				return '<!-- invalid Tabbed Content FX width and / or height in plugin parameters -->';
			}
		}

		if (empty($fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['wmode']])) {
			$wmode = get_option('fxtabbedcontent_wmode');
			if (empty($wmode)) {
				$wmode = $fxtabbedcontent_params['wmode_values']['default'];
			}
		} else {
			$wmode = in_array($fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['wmode']], $fxtabbedcontent_params['wmode_values']['allowed']) ? $fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['wmode']] : $fxtabbedcontent_params['wmode_values']['default'];
		}

		$swf_embed = array(
			'width' => $width,
			'height' => $height,
			'text' => isset($fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['alternative_text']]) ? trim($fxtabbedcontent_attributes[$fxtabbedcontent_params['regexp_match_keys']['alternative_text']]) : '',
			'component_path' => "{$fxtabbedcontent_wp_content_url}/{$plugin_dir}/",
			'swf_name' => 'TabbedContentFX.swf',
			'wmode' => $wmode,
		);
		$swf_embed['swf_path'] = $swf_embed['component_path'].$swf_embed['swf_name'];

		if (!is_feed()) {
			$embed_code = '<div id="flashxmltabbedcontent'.$fxtabbedcontent_params['count'].'">'.$swf_embed['text'].'</div>';
			$embed_code .= '<script type="text/javascript">';
			$embed_code .= "swfobject.embedSWF('{$swf_embed['swf_path']}', 'flashxmltabbedcontent{$fxtabbedcontent_params['count']}', '{$swf_embed['width']}', '{$swf_embed['height']}', '9.0.0.0', '', { folderPath: '{$swf_embed['component_path']}'".($settings_file_name != 'settings.xml' ? ", settingsXML: '".urlencode($settings_file_name)."'" : '')." }, { scale: 'noscale', salign: 'tl', wmode: '{$swf_embed['wmode']}', allowScriptAccess: 'sameDomain', allowFullScreen: true }, {});";
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
			$embed_code .= '<param name="flashvars" value="folderPath='.$swf_embed['component_path'].($settings_file_name != 'settings.xml' ? '&settingsXML='.urlencode($settings_file_name) : '').'"></param>';
			$embed_code .= '<embed type="application/x-shockwave-flash" width="'.$swf_embed['width'].'" height="'.$swf_embed['height'].'" src="'.$swf_embed['swf_path'].'" scale="noscale" salign="tl" wmode="'.$swf_embed['wmode'].'" allowScriptAccess="sameDomain" allowFullScreen="true" flashvars="folderPath='.$swf_embed['component_path'].($settings_file_name != 'settings.xml' ? '&settingsXML='.urlencode($settings_file_name) : '').'"';
			$embed_code .= '></embed>';
			$embed_code .= '</object>';
		}

		return $embed_code;
	}

	function fxtabbedcontent_filter_content($content) {
		return preg_replace_callback('|\[tabbed-content-fx\s*(settings="([^"]+)")?\s*(width="([0-9]+)")?\s*(height="([0-9]+)")?\s*(wmode="([a-z]+)")?\s*\](.*)\[/tabbed-content-fx\]|i', 'fxtabbedcontent_get_embed_code', $content);
	}

	function fxtabbedcontent_echo_embed_code($settings_xml_path = '', $div_text = '', $width = 0, $height = 0, $wmode = 'transparent') {
		global $fxtabbedcontent_params;
		echo fxtabbedcontent_get_embed_code(array($fxtabbedcontent_params['regexp_match_keys']['settings'] => $settings_xml_path, $fxtabbedcontent_params['regexp_match_keys']['width'] => $width, $fxtabbedcontent_params['regexp_match_keys']['height'] => $height, $fxtabbedcontent_params['regexp_match_keys']['wmode'] => $wmode, $fxtabbedcontent_params['regexp_match_keys']['alternative_text'] => $div_text));
	}

	function fxtabbedcontent_load_swfobject_lib() {
		wp_enqueue_script('swfobject');
	}
/* end client side functions */

/* start admin section functions */
	function fxtabbedcontent_admin_menu() {
		add_options_page('Tabbed Content FX Options', 'Tabbed Content FX', 'manage_options', 'fxtabbedcontent', 'fxtabbedcontent_admin_options');
	}

	function fxtabbedcontent_admin_options() {
		 if (!current_user_can('manage_options'))  {
	    wp_die(__('You do not have sufficient permissions to access this page.'));
	  }

	  global $fxtabbedcontent_params;

	  $fxtabbedcontent_default_path = get_option('fxtabbedcontent_path');
	  if ($fxtabbedcontent_default_path === false) {
	  	$fxtabbedcontent_default_path = 'flashxml/tabbed-content-fx';
	  }

 	  $fxtabbedcontent_default_wmode = get_option('fxtabbedcontent_wmode');
	  if ($fxtabbedcontent_default_wmode === false) {
	  	$fxtabbedcontent_default_wmode = $fxtabbedcontent_params['wmode_values']['default'];
	  }
?>
<div class="wrap">
	<h2>Tabbed Content FX</h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row" style="width: 40em;">SWF and assets path is <?php echo basename(WP_CONTENT_DIR); ?>/</th>
				<td><input type="text" style="width: 25em;" name="fxtabbedcontent_path" value="<?php echo $fxtabbedcontent_default_path; ?>" /></td>
			</tr>
			<tr>
				<th scope="row" style="width: 40em;">SWF wmode parameter</th>
				<td>
					<select style="width: 27.5em;" name="fxtabbedcontent_wmode">
<?php
		foreach ($fxtabbedcontent_params['wmode_values']['allowed'] as $fxtabbedcontent_allowed_wmode_value) {
?>
						<option value="<?php echo $fxtabbedcontent_allowed_wmode_value; ?>"<?php echo $fxtabbedcontent_allowed_wmode_value == $fxtabbedcontent_default_wmode ? ' selected="selected"' : ''; ?>><?php echo $fxtabbedcontent_allowed_wmode_value; ?></option>
<?php
		}
?>
					</select>
				</td>
			</tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="fxtabbedcontent_path,fxtabbedcontent_wmode" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
<?php
	}
/* end admin section functions */

/* start widget class */
class tabbedcontentFXWidget extends WP_Widget {
	function tabbedcontentFXWidget() {
		parent::WP_Widget(false, $name = 'Tabbed Content FX');
	}

	function widget($args, $instance) {
		echo $before_widget;
		echo fxtabbedcontent_echo_embed_code($instance['settings_xml_path'], $instance['div_text'], $instance['width'], $instance['height'], $instance['wmode']);
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['settings_xml_path'] = $new_instance['settings_xml_path'];
		$instance['div_text'] = $new_instance['div_text'];
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['height'] = strip_tags($new_instance['height']);
		$instance['wmode'] = strip_tags($new_instance['wmode']);

		return $instance;
	}

	function form($instance) {
		global $fxtabbedcontent_params;

		$settings_xml_path = esc_attr($instance['settings_xml_path']);
		$div_text = esc_attr($instance['div_text']);
		$width = esc_attr($instance['width']);
		$height = esc_attr($instance['height']);
		$wmode = esc_attr($instance['wmode']);

		if (empty($wmode)) {
			$wmode = get_option('fxtabbedcontent_wmode');
			if (empty($wmode)) {
				$wmode = $fxtabbedcontent_params['wmode_values']['default'];
			}
		}

		$plugin_dir = get_option('fxtabbedcontent_path');
		if ($plugin_dir === false) {
			$plugin_dir = 'flashxml/tabbed-content-fx';
		}
?>
            <p>
            	<label for="<?php echo $this->get_field_id('settings_xml_path'); ?>">
            		<?php _e('Settings XML in:'); ?> <?php echo basename(WP_CONTENT_DIR)."/{$plugin_dir}/"; ?>
            		<input class="widefat" id="<?php echo $this->get_field_id('settings_xml_path'); ?>" name="<?php echo $this->get_field_name('settings_xml_path'); ?>" type="text" value="<?php echo $settings_xml_path; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('div_text'); ?>">
            		<?php _e('Alternative content:'); ?>
            		<textarea class="widefat" id="<?php echo $this->get_field_id('div_text'); ?>" name="<?php echo $this->get_field_name('div_text'); ?>"><?php echo $div_text; ?></textarea>
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('width'); ?>">
            		<?php _e('Width:'); ?>
            		<input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" style="margin-left: 4px; width: 178px;" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('height'); ?>">
            		<?php _e('Height:'); ?>
            		<input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" style="margin-left: 4px; width: 174px;" />
            	</label>
            </p>
						<p>
							<label for="<?php echo $this->get_field_id('wmode'); ?>">
								<?php _e('SWF wmode:'); ?>
								<select id="<?php echo $this->get_field_id('wmode'); ?>" name="<?php echo $this->get_field_name('wmode'); ?>" style="width: 137px;">
<?php
		foreach ($fxtabbedcontent_params['wmode_values']['allowed'] as $fxtabbedcontent_allowed_wmode_value) {
?>
									<option value="<?php echo $fxtabbedcontent_allowed_wmode_value; ?>"<?php echo $fxtabbedcontent_allowed_wmode_value == $wmode ? ' selected="selected"' : ''; ?>><?php echo $fxtabbedcontent_allowed_wmode_value; ?></option>
<?php
								}
?>
								</select>
							</label>
						</p>
<?php
	}
}
/* end widget class */

/* start hooks */
	add_filter('the_content', 'fxtabbedcontent_filter_content');
	add_action('init', 'fxtabbedcontent_load_swfobject_lib');
	add_action('admin_menu', 'fxtabbedcontent_admin_menu');
	add_action('widgets_init', create_function('', 'return register_widget("tabbedcontentFXWidget");'));
/* end hooks */

?>