<?php
/*
Plugin Name: tect-media
Plugin URI: https://github.com/Arty2/tect-media
Description: A plugin that restructures WordPress’s default upload and media behaviour, meant to complement the “tect” theme.
Author: Heracles Papatheodorou
License: MIT License
GitHub Plugin URI: https://github.com/Arty2/tect-media
Version: 1.0.0
*/

	//change upload directory
	if ( !is_multisite() ) {
		update_option( 'upload_path', 'media' ); //to-do: add to options page
		define( 'UPLOADS', 'media' ); //define UPLOADS dir
	}
	//don't “Organize my uploads into month- and year-based folders”
	update_option( 'uploads_use_yearmonth_folders', '0' ); // to-do: add to options page


/*--------------------------------------------------------------
Place thumbnails in their individual directores
create a custom WP_Image_Editor that handles the naming of files
based on http://wordpress.stackexchange.com/questions/125784/each-custom-image-size-in-custom-upload-directory
reference: https://github.com/WordPress/WordPress/blob/master/wp-includes/class-wp-image-editor.php
--------------------------------------------------------------*/
	
	function tect_image_editors($editors) {
		array_unshift( $editors, 'WP_Image_Editor_tect' );

		return $editors;
	}

	add_filter( 'wp_image_editors', 'tect_image_editors' );

	require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
	require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';

	//also port to ImageMagick at some point
	class WP_Image_Editor_tect extends WP_Image_Editor_GD {
		public function multi_resize($sizes) {
			if ( 'image/jpeg' == $this->mime_type && function_exists('imageinterlace') ) {
				//Save progressive JPGs
				//https://core.trac.wordpress.org/ticket/21668
				// imagefilter( $this->image, IMG_FILTER_NEGATE ); //DEBUG
				imageinterlace( $this->image, true );
			}

			$sizes = parent::multi_resize($sizes);
			
			$media_dir = trailingslashit( ABSPATH . UPLOADS );

			foreach($sizes as $slug => $data) {
				$default_name = $sizes[ $slug ]['file'];
				// $new_name = $slug . '/' . preg_replace( '#-\d+x\d+\.#', '.', $data['file'] ); //DEBUG
				$new_name = $slug . '/' . str_replace( '-' . $sizes[ $slug ]['width'] . 'x' . $sizes[ $slug ]['height'] . '.', '.', $data['file'] );
				
				//PHP bug messes unicode filenames on Windows: https://core.trac.wordpress.org/ticket/15955

				if ( !is_dir( $media_dir . $slug ) ) {
					mkdir( $media_dir . $slug );
				}
				rename ( $media_dir . $default_name, $media_dir . $new_name );

				$sizes[$slug]['file'] = $new_name;
			}

			return $sizes;
		}
	}




/*--------------------------------------------------------------
Image related
--------------------------------------------------------------*/
	//TinyMCE styles
	//add_editor_style( plugins_url( 'editor.css', __FILE__ ) );
	//document_base_url

	function tect_media_TinyMCE($init) {
		//reference: http://www.tinymce.com/wiki.php/Configuration3x
		// $init['remove_linebreaks'] = FALSE;
		// $init['media_strict'] = FALSE;
		// $init['content_css'] = plugins_url( 'editor.css?' . rand(0,300) , __FILE__ );
		// $init['wpautop'] = TRUE;
		// $init['apply_source_formatting'] = FALSE;
		// $init['convert_urls'] = TRUE;
 		$init['relative_urls'] = TRUE;
		$init['document_base_url'] = site_url('/');
		return $init;
	}

	add_filter('tiny_mce_before_init', 'tect_media_TinyMCE' );

	//remove whatever is too scarcely used: id, alignnone, size-whatever
	//based on http://www.sitepoint.com/wordpress-change-img-tag-html/
	function tect_image_tag_class( $class, $id, $align, $size ) {
		return ( $align != 'none' ? 'align' . $align : '' );
	}

	add_filter( 'get_image_tag_class', 'tect_image_tag_class', 0, 4 );

	//cleanup inserted image code
	function tect_image_tag( $html, $id, $alt, $title ) {
		return preg_replace(
			array(
			'@' . get_bloginfo( 'url' ) . '@', // make src and href relative, borks visual editor!
			'/(width|height)="\d*"\s/', // remove width & height
			),
		array(
			'.',
			'',
			),
		$html );
	}

	add_filter( 'post_thumbnail_html', 'tect_image_tag', 10, 4 );
	add_filter( 'get_image_tag', 'tect_image_tag', 10, 4 );
	add_filter( 'image_send_to_editor', 'tect_image_tag', 10, 4 ); // 'post_thumbnail_html' too ?

	//allow SVGs in the media library
	//based on http://css-tricks.com/snippets/wordpress/allow-svg-through-wordpress-media-uploader/
	function tect_image_svg( $mimes ){
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}

	add_filter( 'upload_mimes', 'tect_image_svg' );

	//fixes display of SVGs in admin
	function tect_image_svg_admin() {
		$css = 'td.media-icon img[src$=".svg"] { width: auto !important; height: auto !important; }';
		echo '<style type="text/css">'.$css.'</style>';
	}

	add_action('admin_head', 'tect_image_svg_admin');

?>