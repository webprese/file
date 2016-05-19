<?php
/**
 * Header Template
 *
 * Here we setup all logic and XHTML that is required for the header section of all screens.
 *
 * @package WooFramework
 * @subpackage Template
 */
 
 // Setup the tag to be used for the header area (`h1` on the front page and `span` on all others).
 $heading_tag = 'span';
 if ( is_front_page() ) { $heading_tag = 'h1'; }
 
 // Get our website's name, description and URL. We use them several times below so lets get them once.
 $site_title = get_bloginfo( 'name' );
 $site_url = home_url( '/' );
 $site_description = get_bloginfo( 'description' );
 
 global $woo_options;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=IE8"/>
<title><?php woo_title(); ?></title>
<?php woo_meta(); ?>
<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_url'); ?>" media="all" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php if ( is_singular() ) { wp_enqueue_script( 'comment-reply' ); } ?>
<?php wp_head(); ?>
<?php woo_head(); ?>
<link rel="stylesheet" type="text/css" href="http://mgeonline.com/custom.css" media="all" />

<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','//connect.facebook.net/en_US/fbevents.js');

fbq('init', '1694570230771118');
fbq('track', "PageView");</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1694570230771118&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
</head>
<body <?php body_class(); ?>>
<?php woo_top(); ?>
<div id="wrapper">        
	<?php woo_header_before(); ?>
    
	<div id="header" class="col-full">

 		
		<?php woo_header_inside(); ?>
       
		<div id="logo">
		<?php
			// Website heading/logo and description text.
			if ( isset($woo_options['woo_logo']) && $woo_options['woo_logo'] ) {
				echo '<a href="' . $site_url . '" title="' . $site_description . '"><img src="' . $woo_options['woo_logo'] . '" alt="' . $site_title . '" /></a>' . "\n";
			} // End IF Statement
			
			echo '<' . $heading_tag . ' class="site-title"><a href="' . $site_url . '">' . $site_title . '</a></' . $heading_tag . '>' . "\n";
			if ( $site_description ) { echo '<span class="site-description">' . $site_description . '</span>' . "\n"; }
		?>
		</div><!-- /#logo -->
		<?php if ( ( isset( $woo_options['woo_ad_top'] ) ) && ( $woo_options['woo_ad_top'] == 'true' ) ) { ?>
        <div id="topad">
        
		<?php if ( ( isset( $woo_options['woo_ad_top_adsense'] ) ) && ( $woo_options['woo_ad_top_adsense'] != "") ) { 
            echo stripslashes(get_option('woo_ad_top_adsense'));             
        } else { ?>
            <a href="<?php echo get_option('woo_ad_top_url'); ?>"><img src="<?php echo $woo_options['woo_ad_top_image']; ?>" alt="" /></a>
        <?php } ?>		   	
        <!-- /#topad -->
        <?php } ?>


	</div><!-- /#header -->
	<?php woo_header_after(); ?>
	<div id="headerright">
	       	       	<h3 id="phonenumber" class="floatright"><img src="http://mgeonline.com/wp/wp-content/uploads/2015/08/phone1.png" alt="phone" width="23" height="23" /> Toll Free: 800-640-1140 </br> <img  src="http://mgeonline.com/wp/wp-content/uploads/2015/08/phone1.png" alt="phone" width="23" height="23" /> Local: 727-530-4277</h3>
					
					<form method="get" id="searchform" action="<?php bloginfo('home'); ?>/">
					<div style="float: right; margin-top: -26px;"><input type="text" size="18" value="<?php echo wp_specialchars($s, 1); ?>" name="s" id="s" />
					<input type="submit" id="searchsubmit" value="Search" class="btn" />
					</div>
</form> 

	</div>



	<div id="secondmenubar">
		<?php
		if ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'top-right-menu' ) ) {
			wp_nav_menu( array( 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => 'top-right-nav', 'menu_class' => '', 'theme_location' => 'top-right-menu' ) );
		}
		?>
	</div>