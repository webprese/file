<?php 

class PageFlipUpdate {
	# URL to check for updates, this is where the index.php script goes
	var $api_url;

	# Type of package to be updated
	var $package_type;

	var $plugin_slug;
	var $plugin_file;

	function PageFlipUpdate($api_url, $type, $slug, $file = null) {
		$this->api_url = $api_url;
		$this->package_type = $type;
		$this->plugin_slug = $slug;
		if ($file === null)
			$file = $slug.'.php';
		$this->plugin_file = $slug.'/'.$file;
	}

	function print_api_result() {
		print_r($res);
		return $res;
	}

	function check_for_plugin_update($checked_data) {
		if (empty($checked_data->checked))
			return $checked_data;
		
		$request_args = array(
			'slug' => $this->plugin_slug,
			'version' => $checked_data->checked[$this->plugin_file],
			'package_type' => $this->package_type,
		);

		$request_string = $this->prepare_request('basic_check', $request_args);
		
		
		$raw_response = wp_remote_post($this->api_url, $request_string);

		if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)) {
			$response = unserialize($raw_response['body']);

			if (is_object($response) && !empty($response)) 
				$checked_data->response[$this->plugin_file] = $response;
		}
		
		return $checked_data;
	}

	function plugins_api_call($def, $action, $args) {
		if ($args->slug != $this->plugin_slug)
			return false;
		
		
		$plugin_info = get_site_transient('update_plugins');
		$current_version = $plugin_info->checked[$this->plugin_file];
		$args->version = $current_version;
		$args->package_type = $this->package_type;
		
		$request_string = $this->prepare_request($action, $args);
		
		$request = wp_remote_post($this->api_url, $request_string);
		
		if (is_wp_error($request)) {
			$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
		} else {
			$res = unserialize($request['body']);
			
			if ($res === false)
				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
		}
		
		return $res;
	}

	function prepare_request($action, $args) {
		global $wp_version;
		$site_url = site_url();

		$wp_info = array(
			'site-url' => $site_url,
			'version' => $wp_version,
		);

		return array(
			'body' => array(
				'action' => $action, 'request' => serialize($args),
				'api-key' => md5($site_url),
				'wp-info' => serialize($wp_info),
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);
	}
}

?>