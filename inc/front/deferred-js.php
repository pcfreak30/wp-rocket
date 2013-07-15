<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Remove tags of deferred JavaScript files 
 *
 * since 1.1.0
 *
 */

function rocket_exclude_deferred_js( $buffer )
{
	$options = get_option( WP_ROCKET_SLUG );

	if( !count( $options['deferred_js_files'] ) )
		return $buffer;

	// Get all JS files with this regex
    preg_match_all( '/<script.+src=.+(\.js).+><\/script>/iU', $buffer, $tags_match );
	
    foreach ( $tags_match[0] as $tag ) {

		// Get link of the file
        preg_match('/src=[\'"]([^\'"]+)/', $tag, $src_match );
		$url = preg_replace( '#\?.*$#', '', $src_match[1] );
		
		// Get all js files to remove
		$deferred_js_files = apply_filters( 'rocket_minify_deferred_js', $options['deferred_js_files'] );

    	// Check if this file is deferred loading
		if( in_array( $url, $deferred_js_files ) )
			$buffer = str_replace( $tag, '', $buffer );

    }

	return $buffer;
}



/**
 * Insert LABjs deferred process in footer
 *
 * since 1.1.0
 *
 */

add_action( 'wp_footer', 'rocket_insert_deferred_js', PHP_INT_MAX );
function rocket_insert_deferred_js( $buffer )
{
	$options = get_option( WP_ROCKET_SLUG );

	if( !count( $options['deferred_js_files'] ) )
		return false;

	$labjs_src 	       = apply_filters( 'rocket_labjs_src', '//cdnjs.cloudflare.com/ajax/libs/labjs/2.0.3/LAB.min.js' );
	$labjs_options     = apply_filters( 'rocket_labjs_options', array( 'AlwaysPreserveOrder' => true ) );
	$deferred_js_files = apply_filters( 'rocket_labjs_deferred_js', $options['deferred_js_files'] );

	$defer  = '<script type="text/javascript" src="' . $labjs_src . '"></script>';
	$defer .= '<script type="text/javascript">';
	$defer .= '$LAB';
	
	// Set LABjs options
	// All options is available in http://labjs.com/documentation.php#optionsobject
	if( count( $labjs_options ) )
		$defer .= '.setOptions(' . json_encode( $labjs_options ) . ')';

	foreach( $deferred_js_files as $k => $js )
	{
		$wait 	= $options['deferred_js_wait'][$k] == '1' ? '.wait(' . esc_js( apply_filters( 'rocket_labjs_wait_callback', false, $js ) ) . ')' : '';
		$defer .= '.script("' . esc_js( $js ) . '")' . $wait;
	}

	$defer .= ';</script>';
	echo $defer;
}