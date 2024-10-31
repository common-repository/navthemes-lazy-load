<?php
/**
Plugin Name: NavThemes Lazy Load
Plugin URI: https://navthemes.com/navthemes-lazyload
Description: This plugin helps to improve loading speed and page insight by implemeting lazy load.
Version: 1.0
Author: navthemes
Author URI: https://www.navthemes.com/
License: GPLv2 or later
Text Domain: navthemes-lazyload
*/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


// Lets add JS here
if(!function_exists('NavThemes_lazyload_script')) :

function NavThemes_lazyload_script(){

 	wp_enqueue_script( 'jdsoftvera-lazyload-script',  plugin_dir_url( __FILE__ ) . 'js/lazysizes.min.js' );

 }

 add_action( 'wp_enqueue_scripts', 'NavThemes_lazyload_script' );

endif;

/*
	Lazy Load Function
*/

// Tweaking Some atts to Make JS Work

	// Post Thumbnail or anything which calls wp_get_attachment_image_attributes

if(!function_exists('NavThemes_lazyload')) :	
  
 function NavThemes_lazyload( $atts ) {
                      
  // data-srcset
  $atts['data-srcset'] = $atts['srcset'];
  $atts['data-src'] = $atts['src'];
  $atts['class'] .= ' lazyload ' ;

  // unset
  unset($atts['srcset']);
  unset($atts['src']);

  // Add a Pre-load image here
  //$atts['src'] = plugin_dir_url( __FILE__ ).'loader.gif';

  return $atts;
 
 }

add_filter( 'wp_get_attachment_image_attributes', 'NavThemes_lazyload', 10, 1 );

endif;

// lets do now Directly in Content
if(!function_exists('NavThemes_lazyloadContent')):

	function NavThemes_lazyloadContent($content) {


	    // Bail if there is no content to work with.
    if ( ! $content ) {
        return $content;
    }

    // Create an instance of DOMDocument.
    $dom = new \DOMDocument();

    // Supress errors due to malformed HTML.
    // See http://stackoverflow.com/a/17559716/3059883
    $libxml_previous_state = libxml_use_internal_errors( true );

    // Populate $dom with $content, making sure to handle UTF-8.
    // Also, make sure that the doctype and HTML tags are not added to our
    // HTML fragment. http://stackoverflow.com/a/22490902/3059883
    $dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ),
          LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

    // Restore previous state of libxml_use_internal_errors() now that we're done.
    libxml_use_internal_errors( $libxml_previous_state );

    // Create an instance of DOMXpath.
    $xpath = new \DOMXpath( $dom );

    // Get images then loop through and add additional classes.
    $imgs = $xpath->query( "//img" );
    foreach ( $imgs as $img ) {

    	// lets set get attrs
    	$img->setAttribute( 'data-src', $img->getAttribute( 'src' ) );
		$img->setAttribute( 'data-srcset', $img->getAttribute( 'srcset' ) );

		// Remove Now.
        $img->removeAttribute( 'src' );
        $img->removeAttribute( 'srcset');
	
		  // Add a Pre-load image here
     //$img->setAttribute( 'src', plugin_dir_url( __FILE__ ).'loader.gif' );
		    	
    	// Lets add Class here
        $existing_class = $img->getAttribute( 'class' );
        $img->setAttribute( 'class', "{$existing_class} lazyload " );
    }

    // Save and return updated HTML.
    $new_content = $dom->saveHTML();
    return $new_content;


	}

	add_filter('the_content','NavThemes_lazyloadContent');

endif;


