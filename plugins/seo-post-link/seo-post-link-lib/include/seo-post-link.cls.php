<?php
/* 
 * License: Copyright (c) 2008 Pawan Agrawal. All rights reserved.
 *
 * This code is part of commercial software and for your personal use 
 * only. You are not allowed to resell or distribute this script.
 *
 */
 
/**
 * SEO Post Link - SEO Post Link
 * Holds all the necessary functions and variables
 */
class ShortPostLinks {
	
	/**
	 * Called when plugin is activated. Adds option value to the options table.
	 * @access public
	 */	
	function __splActivate() {
		$omitted_words = file(SPL_FULLPATH.$this->spl_omitwords_file, FILE_IGNORE_NEW_LINES);
		
		foreach ( (array) $omitted_words as $key => $word ) {
			$omitted_words[$key] = trim(strtolower($word));
		}
		add_option('spl_options', $this->default_settings, 'SEO Post Link maximum slug length', 'no');
		add_option('spl_omitted_words', $omitted_words, 'SEO Post Link omitted word list', 'no');				
	}
	
	/**
	 * returns new post title
	 * @param string $spl_title
	 * @access public
	 */
	 function __splTrimSlug($spl_title) {
			$words = explode(' ',$spl_title);
			$spl_title_new = '';
			for ( $i = 0; $i < count($words); $i++ ) {
				// Omit the word if found in omitted word list
				if( !in_array(trim(strtolower($words[$i])), $this->spl_omitted_words) ) {
					// Omit short words
					if ( $this->spl_options['spl_remove_short_word'] && (strlen(trim($words[$i])) <= $this->spl_options['spl_min_word_length']) ) {
						continue;
					}
					$spl_title_new = $spl_title_new.' '.$words[$i];
					// Exit if title length exceeds spl_max_length
					if ( strlen($spl_title_new) >= $this->spl_options['spl_max_length'] ) {
						break;
					}
				}
			}
			return $spl_title_new;	 	
	 }	
	 
	 /**
	 * Carries all the operations
	 * 
	 */
	 function __splOptionsPg() {
			$spl_post_data = $_POST['spl'];
			if ( $spl_post_data['save_options'] ) {
				$this->spl_options['spl_max_length']    = intval($spl_post_data['spl_max_length']);
				$this->spl_options['spl_force_removal'] = $spl_post_data['spl_force_removal'];
				$the_omitted_words = explode("\n", trim($spl_post_data['spl_omitted_words']));
				foreach( (array) $the_omitted_words as $key => $word ) {
					$this->spl_omitted_words[$key] = trim(stripslashes($word));
				}
				update_option("spl_options", $this->spl_options);
				update_option("spl_omitted_words", $this->spl_omitted_words);
				$msg = "Options Saved.";
			}
			if ( $spl_post_data['save_adv_options'] ) {
				$this->spl_options['spl_remove_short_word'] = $spl_post_data['spl_remove_short_word'];
				$this->spl_options['spl_min_word_length']   = $spl_post_data['spl_min_word_length'];
				update_option("spl_options", $this->spl_options);
				$msg = "Options Saved.";
			}
			if ( trim($msg) !== '' ) {
				echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
			}
			$force_removal_chk = '';
			$remove_short_word_chk = '';
			if ( $this->spl_options['spl_force_removal'] == 1 ) {
				$force_removal_chk = ' checked ';
			}
			if ( $this->spl_options['spl_remove_short_word'] == 1 ) {
				$remove_short_word_chk = ' checked ';
			}
			?>
			<script type="text/javascript">
			<!--
			function splShowHide(Div,Img) {
				var divCtrl = document.getElementById(Div);
				var Img     = document.getElementById(Img);
				if(divCtrl.style=="" || divCtrl.style.display=="none") {
					divCtrl.style.display = "block";
					Img.src = '<?php echo $spl_fullpath?>images/minus.gif';
				}
				else if(divCtrl.style!="" || divCtrl.style.display=="block") {
					divCtrl.style.display = "none";
					Img.src = '<?php echo $spl_fullpath?>images/plus.gif';
				}
			}
			//-->
			</script>
			<div class="wrap">
			 <?php $this->splHeader(); ?>
				<br>
				<table width=100% align=center cellpadding=5 cellspacing=1>
					<tr>
						<td valign=top style="text-align:left">
<form name="spl_form" method="post">
			 <table width="580" cellpadding="4" cellspacing="2" border="0" style="border:1px solid #dfdfdf">
			  <tr class="alternate">
			   <td width="25%"><strong><?php echo 'Max Slug Length'; ?> :</strong></td>
			   <td><input type="text" name="spl[spl_max_length]" id="spl_max_length" value="<?php echo $this->spl_options['spl_max_length'];?>" style="width:32px" maxlength="7" /> characters</td>
			  </tr>
			  <tr>
			   <td colspan="2"><input type="checkbox" name="spl[spl_force_removal]" id="spl_force_removal" value="1" <?php echo $force_removal_chk;?> /> 
			   <?php echo 'Force removal of unncessary words even if the default slug is shorter than "max slug length"'; ?></td>
			  </tr>
			  <tr class="alternate">
			   <td><strong><?php echo 'Unnecessary Words'; ?> :</strong></td>
			   <td><textarea name="spl[spl_omitted_words]" id="spl_omitted_words" rows="12" cols="40" style="width:260px"><?php echo trim(implode("\n", $this->spl_omitted_words));?></textarea></td>
			  </tr>
			  <tr>
			   <td colspan="2"><input type="submit" name="spl[save_options]" value="<?php echo 'Save Options'; ?>" class="button" /></td>
			  </tr>
			 </table><br />
			 </form>
			 
			 <form name="spl_form_adv" method="post">
			 <h3><a name="spldv" href="#spldv" onclick="splShowHide('spl_adv_option','spl_adv_img');"><img src="<?php echo $spl_fullpath?>images/plus.gif" id="spl_adv_img" border="0" /><?php echo 'More Options (Optional)'; ?></a></h3>
			 <div id="spl_adv_option" style="display:none">
			 <table width="580" cellpadding="4" cellspacing="2" border="0" style="border:1px solid #dfdfdf">
			  <tr class="alternate">
			   <td><input type="checkbox" name="spl[spl_remove_short_word]" id="spl_remove_short_word" value="1" <?php echo $remove_short_word_chk;?> /> Remove short words of length upto 
			   <input type="text" name="spl[spl_min_word_length]" id="spl_min_word_length" value="<?php echo $this->spl_options['spl_min_word_length'];?>" style="width:25px" maxlength="7" /> characters</td>
			  </tr>
			  <tr>
			   <td><input type="submit" name="spl[save_adv_options]" value="<?php echo 'Save'; ?>" class="button" /></td>
			  </tr>
			 </table>
			 </div>
			 </form>						
						</td>
						<td width="27%" rowspan="2" valign="top">
						<!--MBP Support-->
						<?php include_once('mbp-support-pg.php'); ?>
						<!--MBP end Support-->
						</td>
					</tr>
					<tr>
						<td width=73% valign="top">
<script type="text/javascript" charset="utf-8">
  var is_ssl = ("https:" == document.location.protocol);
  var asset_host = is_ssl ? "https://s3.amazonaws.com/getsatisfaction.com/" : "http://s3.amazonaws.com/getsatisfaction.com/";
  document.write(unescape("%3Cscript src='" + asset_host + "javascripts/feedback-v2.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<script type="text/javascript" charset="utf-8">
  var feedback_widget_options = {};

  feedback_widget_options.display = "inline";  
  feedback_widget_options.company = "maxblogpress";
  
  
  feedback_widget_options.style = "idea";
  feedback_widget_options.product = "maxblogpress_seo_post_link";
  
  
  
  
  feedback_widget_options.limit = "3";
  
  GSFN.feedback_widget.prototype.local_base_url = "http://community.maxblogpress.com";
  GSFN.feedback_widget.prototype.local_ssl_base_url = "http://community.maxblogpress.com";
  

  var feedback_widget = new GSFN.feedback_widget(feedback_widget_options);
</script>						
						</td>
					</tr>
				</table>
			 
			<?php $this->splFooter(); ?>
			</div>
			<?php
		}	 	
	
	/**
	 * Plugin registration form
	 * @access public 
	 */
	function splRegistrationForm($form_name, $submit_btn_txt='Register', $name, $email, $hide=0, $submit_again='') {
		$wp_url = get_bloginfo('wpurl');
		$wp_url = (strpos($wp_url,'http://') === false) ? get_bloginfo('siteurl') : $wp_url;
		$thankyou_url = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'];
		$onlist_url   = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'].'&amp;mbp_onlist=1';
		if ( $hide == 1 ) $align_tbl = 'left';
		else $align_tbl = 'center';
		?>
		
		<?php if ( $submit_again != 1 ) { ?>
		<script><!--
		function trim(str){
			var n = str;
			while ( n.length>0 && n.charAt(0)==' ' ) 
				n = n.substring(1,n.length);
			while( n.length>0 && n.charAt(n.length-1)==' ' )	
				n = n.substring(0,n.length-1);
			return n;
		}
		function splValidateForm_0() {
			var name = document.<?php echo $form_name;?>.name;
			var email = document.<?php echo $form_name;?>.from;
			var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			var err = ''
			if ( trim(name.value) == '' )
				err += '- Name Required\n';
			if ( reg.test(email.value) == false )
				err += '- Valid Email Required\n';
			if ( err != '' ) {
				alert(err);
				return false;
			}
			return true;
		}
		//-->
		</script>
		<?php } ?>
		<table align="<?php echo $align_tbl;?>">
		<form name="<?php echo $form_name;?>" method="post" action="http://www.aweber.com/scripts/addlead.pl" <?php if($submit_again!=1){;?>onsubmit="return splValidateForm_0()"<?php }?>>
		 <input type="hidden" name="unit" value="maxbp-activate">
		 <input type="hidden" name="redirect" value="<?php echo $thankyou_url;?>">
		 <input type="hidden" name="meta_redirect_onlist" value="<?php echo $onlist_url;?>">
		 <input type="hidden" name="meta_adtracking" value="spl-m-activate">
		 <input type="hidden" name="meta_message" value="1">
		 <input type="hidden" name="meta_required" value="from,name">
	 	 <input type="hidden" name="meta_forward_vars" value="1">	
		 <?php if ( $submit_again != '' ) { ?> 	
		 <input type="hidden" name="submit_again" value="1">
		 <?php } ?>		 
		 <?php if ( $hide == 1 ) { ?> 
		 <input type="hidden" name="name" value="<?php echo $name;?>">
		 <input type="hidden" name="from" value="<?php echo $email;?>">
		 <?php } else { ?>
		 <tr><td>Name: </td><td><input type="text" name="name" value="<?php echo $name;?>" size="25" maxlength="150" /></td></tr>
		 <tr><td>Email: </td><td><input type="text" name="from" value="<?php echo $email;?>" size="25" maxlength="150" /></td></tr>
		 <?php } ?>
		 <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="<?php echo $submit_btn_txt;?>" class="button" /></td></tr>
		</form>
		</table>
		<?php
	}
	
	/**
	 * Register Plugin - Step 2
	 * @access public 
	 */
	function splRegister_2($form_name='frm2',$name,$email) {
		$msg = 'You have not clicked on the confirmation link yet. A confirmation email has been sent to you again. Please check your email and click on the confirmation link to activate the plugin.';
		if ( trim($_GET['submit_again']) != '' && $msg != '' ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
		}
		?>
		<div class="wrap"><h2> <?php echo SPL_NAME.' '.SPL_VERSION; ?></h2>
		 <center>
		 <table width="640" cellpadding="5" cellspacing="1" bgcolor="#ffffff" style="border:1px solid #e9e9e9">
		  <tr><td align="center"><h3>Almost Done....</h3></td></tr>
		  <tr><td><h3>Step 1:</h3></td></tr>
		  <tr><td>A confirmation email has been sent to your email "<?php echo $email;?>". You must click on the link inside the email to activate the plugin.</td></tr>
		  <tr><td><strong>The confirmation email will look like:</strong><br /><img src="http://www.maxblogpress.com/images/activate-plugin-email.jpg" vspace="4" border="0" /></td></tr>
		  <tr><td>&nbsp;</td></tr>
		  <tr><td><h3>Step 2:</h3></td></tr>
		  <tr><td>Click on the button below to Verify and Activate the plugin.</td></tr>
		  <tr><td><?php $this->splRegistrationForm($form_name.'_0','Verify and Activate',$name,$email,$hide=1,$submit_again=1);?></td></tr>
		 </table>
		 <p>&nbsp;</p>
		 <table width="640" cellpadding="5" cellspacing="1" bgcolor="#ffffff" style="border:1px solid #e9e9e9">
           <tr><td><h3>Troubleshooting</h3></td></tr>
           <tr><td><strong>The confirmation email is not there in my inbox!</strong></td></tr>
           <tr><td>Dont panic! CHECK THE JUNK, spam or bulk folder of your email.</td></tr>
           <tr><td>&nbsp;</td></tr>
           <tr><td><strong>It's not there in the junk folder either.</strong></td></tr>
           <tr><td>Sometimes the confirmation email takes time to arrive. Please be patient. WAIT FOR 6 HOURS AT MOST. The confirmation email should be there by then.</td></tr>
           <tr><td>&nbsp;</td></tr>
           <tr><td><strong>6 hours and yet no sign of a confirmation email!</strong></td></tr>
           <tr><td>Please register again from below:</td></tr>
           <tr><td><?php $this->splRegistrationForm($form_name,'Register Again',$name,$email,$hide=0,$submit_again=2);?></td></tr>
           <tr><td><strong>Help! Still no confirmation email and I have already registered twice</strong></td></tr>
           <tr><td>Okay, please register again from the form above using a DIFFERENT EMAIL ADDRESS this time.</td></tr>
           <tr><td>&nbsp;</td></tr>
           <tr>
             <td><strong>Why am I receiving an error similar to the one shown below?</strong><br />
                 <img src="http://www.maxblogpress.com/images/no-verification-error.jpg" border="0" vspace="8" /><br />
               You get that kind of error when you click on &quot;Verify and Activate&quot; button or try to register again.<br />
               <br />
               This error means that you have already subscribed but have not yet clicked on the link inside confirmation email. In order to  avoid any spam complain we don't send repeated confirmation emails. If you have not recieved the confirmation email then you need to wait for 12 hours at least before requesting another confirmation email. </td>
           </tr>
           <tr><td>&nbsp;</td></tr>
           <tr><td><strong>But I've still got problems.</strong></td></tr>
           <tr><td>Stay calm. <strong><a href="http://www.maxblogpress.com/contact-us/" target="_blank">Contact us</a></strong> about it and we will get to you ASAP.</td></tr>
         </table>
		 </center>		
		<p style="text-align:center;margin-top:3em;"><strong><?php echo SPL_NAME.' '.SPL_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	    </div>
		<?php
	}

	/**
	 * Register Plugin - Step 1
	 * @access public 
	 */
	function splRegister_1($form_name='frm1') {
		global $userdata;
		$name  = trim($userdata->first_name.' '.$userdata->last_name);
		$email = trim($userdata->user_email);
		?>
		<div class="wrap"><h2> <?php echo SPL_NAME.' '.SPL_VERSION; ?></h2>
		
		   <table align="center" width="680" border="0" cellspacing="1" cellpadding="3" style="border:2px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
             <tr>
               <td>
			   
			 <table width="620" cellpadding="5" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff; text-align:left;margin-left:20px">
		  <tr><td align="center"><h3>Please register the plugin to activate it. (Registration is free)</h3></td></tr>
		  <tr><td align="left"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>In addition you'll receive complimentary subscription to MaxBlogPress Newsletter which will give you many tips and tricks to attract lots of visitors to your blog.</td>
            </tr>
          </table></td>
		  </tr>
		  <tr><td align="center"><strong>Fill the form below to register the plugin:</strong></td></tr>
		  <tr><td align="center"><?php $this->splRegistrationForm($form_name,'Register',$name,$email);?></td>
		  </tr>
		  <tr><td align="center"><font size="1">[ Your contact information will be handled with the strictest confidence <br />and will never be sold or shared with third parties.Also, you can unsubscribe at anytime.]</font></td></td></tr>
		 </table>			   
			   
			   </td>
             </tr>
           </table>			   		
		
		 
	
		<p style="text-align:center;margin-top:3em;"><strong><?php echo SPL_NAME.' '.SPL_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	    </div>
		<?php
	}				
} 
$ShortPostLinks = new ShortPostLinks();
?>