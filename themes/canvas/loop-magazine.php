<?php
/**
 * Loop - Magazine
 *
 * This is the loop logic file for the "Magazine" page template.
 *
 * @package WooFramework
 * @subpackage Template
 */
 
 global $woo_options, $paged;
 global $more; $more = 0;
 
 remove_action( 'woo_post_inside_before', 'woo_display_post_image', 10 );
 
 // woo_loop_before() is loaded in the main template, to keep the magazine slider out of this file.
			
// Exclude stored duplicates 
$exclude = '';
$cats = array();
$cats_exclude = array();

// Exclude slider posts
if ( $woo_options['woo_slider_magazine_exclude'] == 'true' ) {
	$exclude = get_option( 'woo_exclude' );
}

// Exclude categories
$cats = explode( ',', $woo_options['woo_magazine_exclude'] ); 

if ( !empty($cats) ) {
	foreach ($cats as $cat)
   		$cat_exclude[] = '-' . $cat;
}
   			
// Fix for the WordPress 3.0 "paged" bug.
$paged = 1;
if ( get_query_var( 'paged' ) ) { $paged = get_query_var( 'paged' ); }
if ( get_query_var( 'page' ) ) { $paged = get_query_var( 'page' ); }
$paged = intval( $paged );

$args = array( 'cat' => join( ', ', $cat_exclude ),
			   'paged'=> $paged );

if ( $exclude != '' ) {
	$args['post__not_in'] = $exclude;
}

query_posts( $args );
		
if ( have_posts() ) { $count = 0; $column_count_1 = 0; $column_count_2 = 0;
?>

<div class="fix"></div>

<?php
	while ( have_posts() ) { the_post(); $count++;

		// Featured Starts
		if ( $count <= $woo_options['woo_magazine_feat_posts'] && ! is_paged() ) {

			woo_get_template_part( 'content', 'magazine-featured' );
			
			continue;
		
		}
		
		$column_count_1++; $column_count_2++;
		
?>
		<div class="block<?php if ( $column_count_1 > 1 ) { echo ' last'; $column_count_1 = 0; } ?>">
		<?php woo_get_template_part( 'content', 'magazine-grid' ); ?>
		</div><!--/.block-->
<?php
		
		if ( $column_count_1 == 0 ) { ?><div class="fix"></div><?php } // End IF Statement

	} // End WHILE Loop
} else {
	get_template_part( 'content', 'noposts' );
} // End IF Statement

woo_loop_after();

woo_pagenav();

  add_action( 'woo_post_inside_before', 'woo_display_post_image', 10 );
?>