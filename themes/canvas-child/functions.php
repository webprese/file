<?php
require_once ( TEMPLATEPATH . '/../canvas/functions.php' );
require_once ( get_stylesheet_directory() . '/flowline-widget-woo-subscribe.php' );

function flowline_shortcode_contactform ( $atts, $content = null ) {
	global $post;
		$defaults = array(
						'email' => get_option( 'woo_contactform_email'),
						'subject' => __( 'Message via the contact form', 'woothemes' ),
						'button_text' => apply_filters( 'woo_contact_form_button_text', __( 'Submit', 'woothemes' ) )
						);

		extract( shortcode_atts( $defaults, $atts ) );

		// Extract the dynamic fields as well, if they don't have a value in $defaults.

		$html = '';
		$dynamic_atts = array();
		$formatted_dynamic_atts = array();
		$error_messages = array();

		if ( is_array( $atts ) ) {

			foreach ( $atts as $k => $v ) {

				${$k} = $v;

				$dynamic_atts[$k] = ${$k};

			} // End FOREACH Loop

		} // End IF Statement

		// Parse dynamic fields.

		if ( count( $dynamic_atts ) ) {

			foreach ( $dynamic_atts as $k => $v ) {

				/* Parse the radio buttons.
				--------------------------------------------------*/

				if ( substr( $k, 0, 6 ) == 'radio_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The options.
					if ( array_key_exists( 1, $params ) ) { $options_string = $params[1]; } else { $options_string = ''; } // End IF Statement

					// The default value.
					if ( array_key_exists( 2, $params ) ) { $default_value = $params[2]; } else { $default_value = ''; } // End IF Statement

					// Format the options.
					$options = array();

					if ( $options_string ) {

						$options_raw = explode( ',', $options_string );

						if ( count( $options_raw ) ) {

							foreach ( $options_raw as $o ) {

								$o = trim( $o );

								$is_formatted = strpos( $o, '=' );

								// It's not formatted how we'd like it, so just add the value is both the value and label.
								if ( $is_formatted === false ) {

									$options[$o] = $o;

								// That's more like it. A separate value and label.
								} else {

									$option_data = explode( '=', $o );

									$options[$option_data[0]] = $option_data[1];

								} // End IF Statement

							} // End FOREACH Loop

						} // End IF Statement

					} // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'options' => $options, 'default_value' => $default_value );

				} // End IF Statement

				/* Parse the radio buttons.
				--------------------------------------------------*/

				if ( substr( $k, 0, 6 ) == 'radio_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The options.
					if ( array_key_exists( 1, $params ) ) { $options_string = $params[1]; } else { $options_string = ''; } // End IF Statement

					// The default value.
					if ( array_key_exists( 2, $params ) ) { $default_value = $params[2]; } else { $default_value = ''; } // End IF Statement

					// Format the options.
					$options = array();

					if ( $options_string ) {

						$options_raw = explode( ',', $options_string );

						if ( count( $options_raw ) ) {

							foreach ( $options_raw as $o ) {

								$o = trim( $o );

								$is_formatted = strpos( $o, '=' );

								// It's not formatted how we'd like it, so just add the value is both the value and label.
								if ( $is_formatted === false ) {

									$options[$o] = $o;

								// That's more like it. A separate value and label.
								} else {

									$option_data = explode( '=', $o );

									$options[$option_data[0]] = $option_data[1];

								} // End IF Statement

							} // End FOREACH Loop

						} // End IF Statement

					} // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'options' => $options, 'default_value' => $default_value );

				} // End IF Statement

				/* Parse the checkbox inputs.
				--------------------------------------------------*/

				if ( substr( $k, 0, 9 ) == 'checkbox_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The value of the checkbox.
					if ( array_key_exists( 1, $params ) ) { $value = $params[1]; } else { $value = ''; } // End IF Statement

					// Checked by default?
					if ( array_key_exists( 1, $params ) ) { $checked = $params[2]; } else { $checked = ''; } // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'value' => $value, 'checked' => $checked );

				} // End IF Statement

				/* Parse the text inputs.
				--------------------------------------------------*/

				if ( substr( $k, 0, 5 ) == 'text_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The default text.
					if ( array_key_exists( 1, $params ) ) { $default_text = $params[1]; } else { $default_text = ''; } // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'default_text' => $default_text );

				} // End IF Statement

				/* Parse the select boxes.
				--------------------------------------------------*/

				if ( substr( $k, 0, 7 ) == 'select_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The options.
					if ( array_key_exists( 1, $params ) ) { $options_string = $params[1]; } else { $options_string = ''; } // End IF Statement

					// Format the options.
					$options = array();

					if ( $options_string ) {

						$options_raw = explode( ',', $options_string );

						if ( count( $options_raw ) ) {

							foreach ( $options_raw as $o ) {

								$o = trim( $o );

								$is_formatted = strpos( $o, '=' );

								// It's not formatted how we'd like it, so just add the value is both the value and label.
								if ( $is_formatted === false ) {

									$options[$o] = $o;

								// That's more like it. A separate value and label.
								} else {

									$option_data = explode( '=', $o );

									$options[$option_data[0]] = $option_data[1];

								} // End IF Statement

							} // End FOREACH Loop

						} // End IF Statement

					} // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'options' => $options );

				} // End IF Statement

				/* Parse the textarea inputs.
				--------------------------------------------------*/

				if ( substr( $k, 0, 9 ) == 'textarea_' ) {

					// Separate the parameters.
					$params = explode( '|', $v );

					// The title.
					if ( array_key_exists( 0, $params ) ) { $label = $params[0]; } else { $label = $k; } // End IF Statement

					// The default text.
					if ( array_key_exists( 1, $params ) ) { $default_text = $params[1]; } else { $default_text = ''; } // End IF Statement

					// The number of rows.
					if ( array_key_exists( 2, $params ) ) { $number_of_rows = $params[2]; } else { $number_of_rows = 10; } // End IF Statement

					// The number of columns.
					if ( array_key_exists( 3, $params ) ) { $number_of_columns = $params[3]; } else { $number_of_columns = 10; } // End IF Statement

					// Remove this field from the array, as we're done with it.
					unset( $dynamic_atts[$k] );

					$formatted_dynamic_atts[$k] = array( 'label' => $label, 'default_text' => $default_text, 'number_of_rows' => $number_of_rows, 'number_of_columns' => $number_of_columns );

				} // End IF Statement

			} // End FOREACH Loop

		} // End IF Statement

		/*--------------------------------------------------
		 * Form Processing.
		 *
		 * Here is where we validate the POST'ed data and
		 * format it for sending in an e-mail.
		 *
		 * We then send the e-mail and notify the user.
		--------------------------------------------------*/

		$emailSent = false;

		if ( ( count( $_POST ) > 3 ) && isset( $_POST['submitted'] ) ) {

			$fields_to_skip = array( 'checking', 'submitted', 'sendCopy' );
			$default_fields = array( 'contactName' => '', 'contactEmail' => '', 'contactMessage' => '' );
			$error_responses = array(
									'contactName' => __( 'Please enter your name', 'woothemes' ),
									'contactEmail' => __( 'Please enter your email address (and please make sure it\'s valid)', 'woothemes' ),
									'contactMessage' => __( 'Please enter your message', 'woothemes' )
									);

			$posted_data = $_POST;

			// Check for errors.
			foreach ( array_keys( $default_fields ) as $d ) {

				if ( !isset ( $_POST[$d] ) || $_POST[$d] == '' || ( $d == 'contactEmail' && ! is_email( $_POST[$d] ) ) ) {

					$error_messages[$d] = $error_responses[$d];

				} // End IF Statement

			} // End FOREACH Loop

			// If we have errors, don't do anything. Otherwise, run the processing code.

			if ( count( $error_messages ) ) {} else {

				// Setup e-mail variables.
				$message_fromname = $default_fields['contactName'];
				$message_fromemail = strtolower( $default_fields['contactEmail'] );
				$message_subject = $subject;
				$message_body = $default_fields['contactMessage'] . "\n\r\n\r";

				// Filter out skipped fields and assign default fields.
				foreach ( $posted_data as $k => $v ) {

					if ( in_array( $k, $fields_to_skip ) ) {

						unset( $posted_data[$k] );

					} // End IF Statement

					if ( in_array( $k, array_keys( $default_fields ) ) ) {

						$default_fields[$k] = $v;

						unset( $posted_data[$k] );

					} // End IF Statement

				} // End FOREACH Loop

				// Okay, so now we're left with only the dynamic fields. Assign to a fresh variable.
				$dynamic_fields = $posted_data;

				// Format the default fields into the $message_body.

				foreach ( $default_fields as $k => $v ) {

					if ( $v == '' ) {} else {

						$message_body .= str_replace( 'contact', '', $k ) . ': ' . $v . "\n\r";

					} // End IF Statement

				} // End FOREACH Loop

				// Format the dynamic fields into the $message_body.

				foreach ( $dynamic_fields as $k => $v ) {

					if ( $v == '' ) {} else {

						$value = '';

						if ( substr( $k, 0, 7 ) == 'select_' || substr( $k, 0, 6 ) == 'radio_' ) {

							$message_body .= $formatted_dynamic_atts[$k]['label'] . ': ' . $formatted_dynamic_atts[$k]['options'][$v] . "\n\r";

						} else {

							$message_body .= $formatted_dynamic_atts[$k]['label'] . ': ' . $v . "\n\r";

						} // End IF Statement

					} // End IF Statement

				} // End FOREACH Loop

				// Send the e-mail.
				$headers = __( 'From: ', 'woothemes') . $default_fields['contactName'] . ' <' . $default_fields['contactEmail'] . '>' . "\r\n" . __( 'Reply-To: ', 'woothemes' ) . $default_fields['contactEmail'];

				$emailSent = wp_mail($email, $subject, $message_body, $headers);

				// Send a copy of the e-mail to the sender, if specified.

				if ( isset( $_POST['sendCopy'] ) && $_POST['sendCopy'] == 'true' ) {

					$headers = __( 'From: ', 'woothemes') . $default_fields['contactName'] . ' <' . $default_fields['contactEmail'] . '>' . "\r\n" . __( 'Reply-To: ', 'woothemes' ) . $default_fields['contactEmail'];

					$emailSent = wp_mail($default_fields['contactEmail'], $subject, $message_body, $headers);

				} // End IF Statement

			} // End IF Statement ( count( $error_messages ) )

		} // End IF Statement

		/* Generate the form HTML.
		--------------------------------------------------*/

		$html .= '<div class="post contact-form">' . "\n";

		/* Display message HTML if necessary.
		--------------------------------------------------*/

		// Success message.

		if( isset( $emailSent ) && $emailSent == true ) {

			$html .= do_shortcode( '[box type="tick"]' . __( 'Your email was successfully sent.', 'woothemes' ) . '[/box]' );
			$html .= '<span class="has_sent hide"></span>' . "\n";

		} // End IF Statement

		// Error messages.

		if( count( $error_messages ) ) {

			$html .= do_shortcode( '[box type="alert"]' . __( 'There were one or more errors while submitting the form.', 'woothemes' ) . '[/box]' );

		} // End IF Statement

        // No e-mail address supplied.

        if( $email == '' ) {

			$html .= do_shortcode( '[box type="alert"]' . __( 'E-mail has not been setup properly. Please add your contact e-mail!', 'woothemes' ) . '[/box]' );

		} // End IF Statement

		if ( $email == '' ) {} else {

			$html .= '<form action="'.get_permalink($post->ID).'" id="contactForm" name="contactForm" method="post">' . "\n";

				$html .= '<fieldset class="forms">' . "\n";

			/* Parse the "static" form fields.
			--------------------------------------------------*/

			$contactName = '';
			if( isset( $_POST['contactName'] ) ) { $contactName = $_POST['contactName']; } // End IF Statement

			$contactEmail = '';
			if( isset( $_POST['contactEmail'] ) ) { $contactEmail = $_POST['contactEmail']; } // End IF Statement

			$contactMessage = '';
			if( isset( $_POST['contactMessage'] ) ) { $contactMessage = stripslashes( $_POST['contactMessage'] ); } // End IF Statement

			$html .= '<p><label for="contactName">' . __( 'Name', 'woothemes' ) . '</label>' . "\n";
			$html .= '<input type="text" name="contactName" id="contactName" value="' . esc_attr( $contactName ) . '" class="txt requiredField" />' . "\n";

			if( array_key_exists( 'contactName', $error_messages ) ) {

				$html .= '<span class="error">' . $error_messages['contactName'] . '</span>' . "\n";

			} // End IF Statement

			$html .= '</p>' . "\n";

			$html .= '<p><label for="contactEmail">' . __( 'Email', 'woothemes' ) . '</label>' . "\n";
			$html .= '<input type="text" name="contactEmail" id="contactEmail" value="' . esc_attr( $contactEmail ) . '" class="txt requiredField email" />' . "\n";

			if( array_key_exists( 'contactEmail', $error_messages ) ) {

				$html .= '<span class="error">' . $error_messages['contactEmail'] . '</span>' . "\n";

			} // End IF Statement

			$html .= '</p>' . "\n";

			$html .= '<p class="textarea"><label for="contactMessage">' . __( 'Message', 'woothemes' ) . '</label>' . "\n";
			$html .= '<textarea name="contactMessage" id="contactMessage" rows="20" cols="30" class="textarea requiredField">' . esc_textarea( $contactMessage ) . '</textarea>' . "\n";

			if( array_key_exists( 'contactMessage', $error_messages ) ) {

				$html .= '<span class="error">' . $error_messages['contactMessage'] . '</span>' . "\n";

			} // End IF Statement

			$html .= '</p>' . "\n";

			/* Parse dynamic fields into HTML.
			--------------------------------------------------*/

			if ( count( $formatted_dynamic_atts ) ) {

				foreach ( $formatted_dynamic_atts as $k => $v ) {

					/* Parse the radio buttons.
					--------------------------------------------------*/

					if ( substr( $k, 0, 6 ) == 'radio_' ) {

						/* Generate Select Box Field HTML.
						----------------------------------------------*/

						${$k} = $v['default_value'];
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$html .= '<p><label for="' . $k . '">' . $v['label'] . '</label>' . "\n";

							$html .= '<span class="woo-radio-container fl">' . "\n";

							foreach ( $v['options'] as $value => $label ) {

								$html .= '<input type="radio" name="' . $k . '" class="radio-button woo-input-radio" value="' . $value . '"' . checked( $value, ${$k}, false ) . ' />&nbsp;' . $label . '<br />' . "\n";

							} // End FOREACH Loop

							$html .= '</span><!--/.woo-radio-container-->' . "\n";

					} // End IF Statement

					/* Parse the checkbox inputs.
					--------------------------------------------------*/

					if ( substr( $k, 0, 9 ) == 'checkbox_' ) {

						/* Generate Checkbox Input Field HTML.
						----------------------------------------------*/

						${$k} = $v['value'];
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$checked = 0;
						if ( array_key_exists( 'checked', $v ) && $v['checked'] == 'yes' ) { $checked = ${$k}; }

						$html .= '<p class="inline">' . "\n";
						$html .= '<input type="checkbox" value="' . ${$k} . '" name="' . $k . '" id="' . $k . '" class="checkbox input-checkbox woo-input-checkbox"' . checked( $checked, ${$k}, false ) . ' />' . "\n";
						$html .= '<label for="' . $k . '">' . $v['label'] . '</label></p>' . "\n";

					} // End IF Statement

					/* Parse the text inputs.
					--------------------------------------------------*/

					if ( substr( $k, 0, 5 ) == 'text_' ) {

						/* Generate Text Input Field HTML.
						----------------------------------------------*/

						${$k} = $v['default_text'];
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$html .= '<p><label for="' . $k . '">' . $v['label'] . '</label>' . "\n";
						$html .= '<input type="text" value="' . esc_attr( ${$k} ) . '" name="' . $k . '" id="' . $k . '" class="txt input-text textfield woo-input-text" /></p>' . "\n";

					} // End IF Statement

					/* Parse the select boxes.
					--------------------------------------------------*/

					if ( substr( $k, 0, 7 ) == 'select_' ) {

						/* Generate Select Box Field HTML.
						----------------------------------------------*/

						${$k} = '';
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$html .= '<p><label for="' . $k . '">' . $v['label'] . '</label>' . "\n";
						$html .= '<select name="' . $k . '" id="' . $k . '" class="select selectfield woo-select">' . "\n";

							foreach ( $v['options'] as $value => $label ) {

								$selected = '';
								if ( $value == ${$k} ) { $selected = ' selected="selected"'; } // End IF Statement

								$html .= '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . $label . '</option>' . "\n";

							} // End FOREACH Loop

						$html .= '</select></p>' . "\n";

					} // End IF Statement

					/* Parse the textarea inputs.
					--------------------------------------------------*/

					if ( substr( $k, 0, 9 ) == 'textarea_' ) {

						/* Generate Textarea Input Field HTML.
						----------------------------------------------*/

						${$k} = $v['default_text'];
						if ( isset( $_POST[$k] ) ) { ${$k} = trim( strip_tags( $_POST[$k] ) ); } // End IF Statement

						$html .= '<p><label for="' . $k . '">' . $v['label'] . '</label>' . "\n";
						$html .= '<textarea rows="' . $v['number_of_rows'] . '" cols="' . $v['number_of_columns'] . '" name="' . $k . '" id="' . $k . '" class="input-textarea textarea woo-textarea">' . $v['default_text'] . '</textarea></p>' . "\n";

					} // End IF Statement

				} // End FOREACH Loop

			} // End IF Statement

			/* The end of the form.
			----------------------------------------------*/

			$sendCopy = '';
			if(isset($_POST['sendCopy']) && $_POST['sendCopy'] == true) {

				$sendCopy = ' checked="checked"';

			} // End IF Statement

			$html .= '<p class="inline"><input type="checkbox" name="sendCopy" id="sendCopy" value="true"' . $sendCopy . ' /><label for="sendCopy">' . __( 'Send a copy of this email to yourself', 'woothemes' ) . '</label></p>' . "\n";

			$checking = '';
			if(isset($_POST['checking'])) {

				$checking = $_POST['checking'];

			} // End IF Statement

			$html .= '<p class="screenReader"><label for="checking" class="screenReader">' . __('If you want to submit this form, do not enter anything in this field', 'woothemes') . '</label><input type="text" name="checking" id="checking" class="screenReader" value="' . esc_attr( $checking ) . '" /></p>' . "\n";

			$html .= '<p class="buttons"><input type="hidden" name="submitted" id="submitted" value="true" /><input class="submit button" type="submit" value="' . $button_text . '" /></p>';

				$html .= '</fieldset>' . "\n";

			$html .= '</form>' . "\n";

			$html .= '</div><!--/.post .contact-form-->' . "\n";

			$html .= '<div class="fix"></div>' . "\n";

		} // End IF Statement ( $email == '' )

		return $html;

} // End woo_shortcode_contactform()

add_shortcode( 'flowline_contact_form', 'flowline_shortcode_contactform' );



/*-----------------------------------------------------------------------------------*/
/* Subscribe / Connect */
/*-----------------------------------------------------------------------------------*/

if (!function_exists('flowline_woo_subscribe_connect')) {
	function flowline_woo_subscribe_connect($widget = 'false', $title = '', $form = '', $social = '') {

		global $woo_options;

		// Setup title
		if ( $widget != 'true' )
			$title = $woo_options['woo_connect_title'];
		
		// Setup related post (not in widget)
		$related_posts = '';
		if ( $woo_options['woo_connect_related'] == "true" AND $widget != "true" )
			$related_posts = do_shortcode('[related_posts limit="5"]');

?>
	<?php if ( $woo_options['woo_connect'] == "true" OR $widget == 'true' ) : ?> 
	<div id="connect">
		<h3><?php if ( $title ) echo stripslashes( $title ); else _e('Subscribe','woothemes'); ?></h3>
		
		<div <?php if ( $related_posts != '' ) echo 'class="col-left"'; ?>>
			<p><?php if ($woo_options['woo_connect_content'] != '') echo stripslashes($woo_options['woo_connect_content']); else _e('Subscribe to our e-mail newsletter to receive updates.', 'woothemes'); ?></p>
			
			<?php if ( $woo_options['woo_connect_newsletter_id'] != "" AND $form != 'on' ) : ?> 
			<form class="newsletter-form<?php if ( $related_posts == '' ) echo ' fl'; ?>" action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow" onsubmit="window.open('http://feedburner.google.com/fb/a/mailverify?uri=<?php echo $woo_options['woo_connect_newsletter_id']; ?>', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true">
				<input class="email" type="text" name="email" value="<?php _e('E-mail','woothemes'); ?>" onfocus="if (this.value == '<?php _e('E-mail','woothemes'); ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e('E-mail','woothemes'); ?>';}" />
				<input type="hidden" value="<?php echo $woo_options['woo_connect_newsletter_id']; ?>" name="uri"/>
				<input type="hidden" value="<?php bloginfo('name'); ?>" name="title"/>
				<input type="hidden" name="loc" value="en_US"/>			
				<input class="submit button" type="submit" name="submit" value="<?php _e('Submit', 'woothemes'); ?>" />
			</form>
			<?php endif; ?>

			<?php if ( $woo_options['woo_connect_mailchimp_list_url'] != "" AND $form != 'on' AND $woo_options['woo_connect_newsletter_id'] == "" ) : ?> 
			<!-- Begin MailChimp Signup Form -->
			<div id="mc_embed_signup">
				<form class="newsletter-form<?php if ( $related_posts == '' ) echo ' fl'; ?>" action="<?php echo $woo_options['woo_connect_mailchimp_list_url']; ?>" method="post" target="popupwindow" onsubmit="window.open('<?php echo $woo_options['woo_connect_mailchimp_list_url']; ?>', 'popupwindow', 'scrollbars=yes,width=650,height=520');return true">
					<input type="text" name="EMAIL" class="required email" value="<?php _e('E-mail','woothemes'); ?>"  id="mce-EMAIL" onfocus="if (this.value == '<?php _e('E-mail','woothemes'); ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e('E-mail','woothemes'); ?>';}">
					<input type="submit" value="<?php _e('Submit', 'woothemes'); ?>" name="subscribe" id="mc-embedded-subscribe" class="btn submit button">
				</form>
			</div>
			<!--End mc_embed_signup-->
			<?php endif; ?>
			
			<?php if ( $social != 'on' ) : ?>					
			<div class="social<?php if ( $related_posts == '' AND $woo_options['woo_connect_newsletter_id'] != "" ) echo ' fr'; ?>">		    	   		
		   		<?php if ( $woo_options['woo_connect_rss'] == "true" ) { ?>
		   		<a target="_blank" href="<?php if ( $woo_options['woo_feed_url'] ) { echo $woo_options['woo_feed_url']; } else { echo get_bloginfo_rss('rss2_url'); } ?>" class="subscribe"><img src="<?php echo get_template_directory_uri(); ?>/images/ico-social-rss.png" title="<?php _e('Subscribe to our RSS feed', 'woothemes'); ?>" alt=""/></a>
		   		
		   		<?php } if ( $woo_options['woo_connect_twitter'] != "" ) { ?>
		   		<a target="_blank" href="<?php echo $woo_options['woo_connect_twitter']; ?>" class="twitter"><img src="<?php echo get_template_directory_uri(); ?>/images/ico-social-twitter.png" title="<?php _e('Follow us on Twitter', 'woothemes'); ?>" alt=""/></a>
		   		
		   		<?php } if ( $woo_options['woo_connect_facebook'] != "" ) { ?>
		   		<a target="_blank" href="<?php echo $woo_options['woo_connect_facebook']; ?>" class="facebook"><img src="<?php echo get_template_directory_uri(); ?>/images/ico-social-facebook.png" title="<?php _e('Connect on Facebook', 'woothemes'); ?>" alt=""/></a>
		   		
		   		<?php } if ( $woo_options['woo_connect_youtube'] != "" ) { ?>
		   		<a target="_blank" href="<?php echo $woo_options['woo_connect_youtube']; ?>" class="youtube"><img src="<?php echo get_template_directory_uri(); ?>/images/ico-social-youtube.png" title="<?php _e('Watch on YouTube', 'woothemes'); ?>" alt=""/></a>
		   		
		   		<?php } if ( $woo_options['woo_connect_flickr'] != "" ) { ?>
		   		<a target="_blank" href="<?php echo $woo_options['woo_connect_flickr']; ?>" class="flickr"><img src="<?php echo get_template_directory_uri(); ?>/images/ico-social-flickr.png" title="<?php _e('See photos on Flickr', 'woothemes'); ?>" alt=""/></a>
		   		
		   		<?php } if ( $woo_options['woo_connect_linkedin'] != "" ) { ?>
		   		<a target="_blank" href="<?php echo $woo_options['woo_connect_linkedin']; ?>" class="linkedin"><img src="<?php echo get_template_directory_uri(); ?>/images/ico-social-linkedin.png" title="<?php _e('Connect on LinkedIn', 'woothemes'); ?>" alt=""/></a>
		   		
		   		<?php } if ( $woo_options['woo_connect_delicious'] != "" ) { ?>
		   		<a target="_blank" href="<?php echo $woo_options['woo_connect_delicious']; ?>" class="delicious"><img src="<?php echo get_template_directory_uri(); ?>/images/ico-social-delicious.png" title="<?php _e('Discover on Delicious', 'woothemes'); ?>" alt=""/></a>						

		   		<?php } if ( $woo_options[ 'woo_connect_googleplus' ] != "" ) { ?>
		   		<a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_googleplus'] ); ?>" class="googleplus"><img src="<?php echo get_template_directory_uri(); ?>/images/ico-social-googleplus.png" title="<?php _e('View Google+ profile', 'woothemes'); ?>" alt=""/></a>
				<?php } ?>

			</div>
			<?php endif; ?>
			
		</div><!-- col-left -->
		
		<?php if ( $woo_options['woo_connect_related'] == "true" AND $related_posts != '' ) : ?>
		<div class="related-posts col-right">
			<h4><?php _e('Related Posts:', 'woothemes'); ?></h4>
			<?php echo $related_posts; ?>
		</div><!-- col-right -->
		<?php wp_reset_query(); endif; ?>
							
        <div class="fix"></div>
	</div>
	<?php endif; ?>
<?php 
	}
}

function mge_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Header - Right', 'mge' ),
		'id' => 'header-right',
		'description' => __( 'Header - Right', 'mge' ),
		'before_widget' => '<div class="boxes">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="cufon">',
		'after_title' => '</h2>',
	) );
}
add_action( 'widgets_init', 'mge_widgets_init' );

register_nav_menus( array( 'top-right-menu' => __( 'Top Right Menu', 'woothemes' ) ) );

?>