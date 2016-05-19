<?php
wp_admin_css( 'css/global', true );
wp_admin_css('wp-admin', true);
wp_admin_css( 'css/colors', true );
wp_admin_css( 'css/ie', true );
if ( is_multisite() )
	wp_admin_css( 'css/ms', true );
wp_enqueue_script('utils', true);
do_action('admin_head');
?>
<div style="text-align:right;margin:10px;"><input type="button" name="closerefresh" class="closerefresh" value="Close & Refresh Data" onclick="javascript:parent.tb_remove();parent.location.reload(1);" /></div>
<h2>Edit Review</h2>
<?php
	if(!empty($success)){
		echo '<div class="updated" style="padding:10px;"><span class="update-message">'.$success.'</span></div><br />';
	}
	if(!empty($error)){
		echo '<div class="error" style="padding:10px;"><span class="update-message">'.$error.'</span></div><br />';
	}
?>
<div style="padding:10px;">
<form method="post" action="">
	<table cellpadding="5" cellspacing="5">
		<tr valign="top">
			<td>Reviewer Date</td>
			<td><input type="text" name="date_time" id="date_time" value="<?php echo $date_time;?>" /></td>
		</tr>
		<tr valign="top">
			<td>Reviewer Name</td>
			<td><input type="text" name="reviewer_name" id="reviewer_name" value="<?php echo $reviewer_name;?>" /></td>
		</tr>
		<tr valign="top">
			<td>Reviewer Email</td>
			<td><input type="text" name="reviewer_email" id="reviewer_email" value="<?php echo $reviewer_email;?>" /></td>
		</tr>
		<tr valign="top">
			<td>Reviewer IP</td>
			<td><input type="text" name="reviewer_ip" id="reviewer_ip" value="<?php echo $reviewer_ip;?>" /></td>
		</tr>
		<tr valign="top">
			<td>Reviewer Title</td>
			<td><input type="text" name="review_title" id="review_title" value="<?php echo stripslashes($review_title);?>" /></td>
		</tr>
		<tr valign="top">
			<td>Reviewer Text</td>
			<td><textarea name="review_text" id="review_text" rows="1" cols="1" style="width:300px;height:140px;"><?php echo stripslashes($review_text);?></textarea></td>
		</tr>
		<tr valign="top">
			<td>Reviewer Rating</td>
			<td>
				<select name="review_rating" id="review_rating">
				<?php
					for($counterrating=0;$counterrating<=5;$counterrating++){
						if($counterrating==$review_rating)
							echo '<option value="'.$counterrating.'" selected="selected">'.$counterrating.'</option>';
						else
							echo '<option value="'.$counterrating.'">'.$counterrating.'</option>';
					}
				?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<td>Reviewer URL</td>
			<td><input type="text" name="reviewer_url" id="reviewer_url" value="<?php echo $reviewer_url;?>" /></td>
		</tr>
		<tr valign="top">
			<td>Page ID</td>
			<td><input type="text" name="page_id" id="page_id" value="<?php echo $page_id;?>" /></td>
		</tr>
		<tr valign="top">
			<td>Custom Fields</td>
			<td>
				<?php
					$custom_fields = @unserialize($custom_fields);
					foreach($custom_fields_options as $key => $value){
						if(!empty($value))
						    echo $value.': <input type="text" name="custom_'.$key.'" id="custom_'.$key.'" value="'.stripslashes($custom_fields[$value]).'" /><br />';
					}
				?>
			</td>
		</tr>
		<tr valign="top">
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" id="submit" value="Save" /></td>
		</tr>
	</table>
</form>
</div>
<div style="text-align:right;margin:10px;"><input type="button" name="closerefresh" class="closerefresh" value="Close & Refresh Data" onclick="javascript:parent.tb_remove();parent.location.reload(1);" /></div>