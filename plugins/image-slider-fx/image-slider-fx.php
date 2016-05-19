<?php
/*
Plugin Name: Image Slider FX
Plugin URI: http://www.flashxml.net/image-slider.html
Description: One of the most advanced Image Slider on the web. Completely XML customizable without any Flash knowledge. And it's free!
Version: 0.8.1
Author: FlashXML.net
Author URI: http://www.flashxml.net/
License: GPL2
*/

/* start global parameters */
	$fximageslider_params = array(
		'count'	=> 0, // number of Image Slider FX embeds
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
	function fximageslider_get_embed_code($fximageslider_attributes) {
		global $fximageslider_params;
		$fximageslider_params['count']++;

		$fximageslider_wp_content_url = str_replace(array("http://{$_SERVER['HTTP_HOST']}", "https://{$_SERVER['HTTP_HOST']}"), array('', ''), WP_CONTENT_URL);

		$plugin_dir = get_option('fximageslider_path');
		if ($plugin_dir === false) {
			$plugin_dir = 'flashxml/image-slider-fx';
		}
		$plugin_dir = trim($plugin_dir, '/');

		$settings_file_name = !empty($fximageslider_attributes[$fximageslider_params['regexp_match_keys']['settings']]) ? html_entity_decode(urldecode($fximageslider_attributes[$fximageslider_params['regexp_match_keys']['settings']])) : 'settings.xml';

		$settings_wp_content_prefix = $fximageslider_params['read_settings_from_url'] && (strtolower(ini_get('allow_url_fopen')) == 'on' || strtolower(ini_get('allow_url_fopen')) == '1') ? $fximageslider_wp_content_url : WP_CONTENT_DIR;
		$settings_path = "{$settings_wp_content_prefix}/{$plugin_dir}/{$settings_file_name}";

		$width = $height = 0;

		if (function_exists('simplexml_load_file') && ($settings_wp_content_prefix == $fximageslider_wp_content_url || $settings_wp_content_prefix == WP_CONTENT_DIR && file_exists($settings_path))) {
			$data = simplexml_load_file($settings_path);
			if ($data) {
				$width_attributes_array = $data->General_Properties->componentWidth->attributes();
				$width = !empty($width_attributes_array) ? (int)$width_attributes_array['value'] : 0;
				$height_attributes_array = $data->General_Properties->componentHeight->attributes();
				$height = !empty($height_attributes_array) ? (int)$height_attributes_array['value'] : 0;
			}
		}

		if (!($width > 0 && $height > 0)) {
			if ((int)$fximageslider_attributes[$fximageslider_params['regexp_match_keys']['width']] > 0 && (int)$fximageslider_attributes[$fximageslider_params['regexp_match_keys']['height']] > 0) {
				$width = (int)$fximageslider_attributes[$fximageslider_params['regexp_match_keys']['width']];
				$height = (int)$fximageslider_attributes[$fximageslider_params['regexp_match_keys']['height']];
			} else {
				return '<!-- invalid Image Slider FX width and / or height in plugin parameters -->';
			}
		}

		if (empty($fximageslider_attributes[$fximageslider_params['regexp_match_keys']['wmode']])) {
			$wmode = get_option('fximageslider_wmode');
			if (empty($wmode)) {
				$wmode = $fximageslider_params['wmode_values']['default'];
			}
		} else {
			$wmode = in_array($fximageslider_attributes[$fximageslider_params['regexp_match_keys']['wmode']], $fximageslider_params['wmode_values']['allowed']) ? $fximageslider_attributes[$fximageslider_params['regexp_match_keys']['wmode']] : $fximageslider_params['wmode_values']['default'];
		}

		$swf_embed = array(
			'width' => $width,
			'height' => $height,
			'text' => isset($fximageslider_attributes[$fximageslider_params['regexp_match_keys']['alternative_text']]) ? trim($fximageslider_attributes[$fximageslider_params['regexp_match_keys']['alternative_text']]) : '',
			'component_path' => "{$fximageslider_wp_content_url}/{$plugin_dir}/",
			'swf_name' => 'ImageSliderFX.swf',
			'wmode' => $wmode,
		);
		$swf_embed['swf_path'] = $swf_embed['component_path'].$swf_embed['swf_name'];

		if (!is_feed()) {
			$embed_code = '<div id="flashxmlimageslider'.$fximageslider_params['count'].'">'.$swf_embed['text'].'</div>';
			$embed_code .= '<script type="text/javascript">';
			$embed_code .= "swfobject.embedSWF('{$swf_embed['swf_path']}', 'flashxmlimageslider{$fximageslider_params['count']}', '{$swf_embed['width']}', '{$swf_embed['height']}', '9.0.0.0', '', { folderPath: '{$swf_embed['component_path']}'".($settings_file_name != 'settings.xml' ? ", settingsXML: '".urlencode($settings_file_name)."'" : '')." }, { scale: 'noscale', salign: 'tl', wmode: '{$swf_embed['wmode']}', allowScriptAccess: 'sameDomain', allowFullScreen: true }, {});";
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

	function fximageslider_filter_content($content) {
		return preg_replace_callback('|\[image-slider-fx\s*(settings="([^"]+)")?\s*(width="([0-9]+)")?\s*(height="([0-9]+)")?\s*(wmode="([a-z]+)")?\s*\](.*)\[/image-slider-fx\]|i', 'fximageslider_get_embed_code', $content);
	}

	function fximageslider_echo_embed_code($settings_xml_path = '', $div_text = '', $width = 0, $height = 0, $wmode = 'transparent') {
		global $fximageslider_params;
		echo fximageslider_get_embed_code(array($fximageslider_params['regexp_match_keys']['settings'] => $settings_xml_path, $fximageslider_params['regexp_match_keys']['width'] => $width, $fximageslider_params['regexp_match_keys']['height'] => $height, $fximageslider_params['regexp_match_keys']['wmode'] => $wmode, $fximageslider_params['regexp_match_keys']['alternative_text'] => $div_text));
	}

	function fximageslider_load_swfobject_lib() {
		wp_enqueue_script('swfobject');
	}
/* end client side functions */

/* start admin section functions */
	function fximageslider_admin_menu() {
		add_options_page('Image Slider FX Options', 'Image Slider FX', 'manage_options', 'fximageslider', 'fximageslider_admin_options');
	}

	function fximageslider_admin_options() {
		 if (!current_user_can('manage_options'))  {
	    wp_die(__('You do not have sufficient permissions to access this page.'));
	  }

	  global $fximageslider_params;

	  $fximageslider_default_path = get_option('fximageslider_path');
	  if ($fximageslider_default_path === false) {
	  	$fximageslider_default_path = 'flashxml/image-slider-fx';
	  }

 	  $fximageslider_default_wmode = get_option('fximageslider_wmode');
	  if ($fximageslider_default_wmode === false) {
	  	$fximageslider_default_wmode = $fximageslider_params['wmode_values']['default'];
	  }
?>
<div class="wrap">
	<h2>Image Slider FX</h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row" style="width: 40em;">SWF and assets path is <?php echo basename(WP_CONTENT_DIR); ?>/</th>
				<td><input type="text" style="width: 25em;" name="fximageslider_path" value="<?php echo $fximageslider_default_path; ?>" /></td>
			</tr>
			<tr>
				<th scope="row" style="width: 40em;">SWF wmode parameter</th>
				<td>
					<select style="width: 27.5em;" name="fximageslider_wmode">
<?php
		foreach ($fximageslider_params['wmode_values']['allowed'] as $fximageslider_allowed_wmode_value) {
?>
						<option value="<?php echo $fximageslider_allowed_wmode_value; ?>"<?php echo $fximageslider_allowed_wmode_value == $fximageslider_default_wmode ? ' selected="selected"' : ''; ?>><?php echo $fximageslider_allowed_wmode_value; ?></option>
<?php
		}
?>
					</select>
				</td>
			</tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="fximageslider_path,fximageslider_wmode" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
<?php
	}
/* end admin section functions */

/* start widget class */
class imagesliderFXWidget extends WP_Widget {
	function imagesliderFXWidget() {
		parent::WP_Widget(false, $name = 'Image Slider FX');
	}

	function widget($args, $instance) {
		echo $before_widget;
		echo fximageslider_echo_embed_code($instance['settings_xml_path'], $instance['div_text'], $instance['width'], $instance['height'], $instance['wmode']);
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
		global $fximageslider_params;

		$settings_xml_path = esc_attr($instance['settings_xml_path']);
		$div_text = esc_attr($instance['div_text']);
		$width = esc_attr($instance['width']);
		$height = esc_attr($instance['height']);
		$wmode = esc_attr($instance['wmode']);

		if (empty($wmode)) {
			$wmode = get_option('fximageslider_wmode');
			if (empty($wmode)) {
				$wmode = $fximageslider_params['wmode_values']['default'];
			}
		}

		$plugin_dir = get_option('fximageslider_path');
		if ($plugin_dir === false) {
			$plugin_dir = 'flashxml/image-slider-fx';
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
		foreach ($fximageslider_params['wmode_values']['allowed'] as $fximageslider_allowed_wmode_value) {
?>
									<option value="<?php echo $fximageslider_allowed_wmode_value; ?>"<?php echo $fximageslider_allowed_wmode_value == $wmode ? ' selected="selected"' : ''; ?>><?php echo $fximageslider_allowed_wmode_value; ?></option>
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
	add_filter('the_content', 'fximageslider_filter_content');
	add_action('init', 'fximageslider_load_swfobject_lib');
	add_action('admin_menu', 'fximageslider_admin_menu');
	add_action('widgets_init', create_function('', 'return register_widget("imagesliderFXWidget");'));
/* end hooks */

?>