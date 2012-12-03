<?php
/*
Plugin Name: Extend Visual Composer Plugin Example
Plugin URI: http://wpbakery.com/vc
Description: Extend Visual Composer with your own set of shortcodes.
Version: 0.1
Author: WPBakery
Author URI: http://wpbakery.com
License: GPLv2 or later
*/

/*
This example/starter plugin can be used to speed up Visual Composer plugins creation process.
More information can be found here: http://kb.wpbakery.com/index.php?title=Category:Visual_Composer

In this example all plugin related functions will have a "vc_extend" prefix, make sure to use unique prefix
in your plugin. Otherwise, you (or your users) may experience "Cannot redaclare function" error.
*/

// don't load directly
if (!defined('ABSPATH')) die('-1');

/*
Display notice if Visual Composer is not installed or activated.
*/
if ( !defined('WPB_VC_VERSION') ) { add_action('admin_notices', 'vc_extend_notice'); return; }
function vc_extend_notice() {
  $plugin_data = get_plugin_data(__FILE__);
  echo '
  <div class="updated">
    <p>'.sprintf(__('<strong>%s</strong> requires <strong><a href="http://bit.ly/vcomposer" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'vc_extend'), $plugin_data['Name']).'</p>
  </div>';
}

/*
Load plugin css and javascript files
*/
add_action('wp_enqueue_scripts', 'vc_extend_js_css');
function vc_extend_js_css() {
  wp_register_style( 'vc_extend_style', plugins_url('vc_extend.css', __FILE__) );
  wp_enqueue_style( 'vc_extend_style' );
  
  // If you need any javascript files on front end, here is how you can load them.
  //wp_enqueue_script( 'vc_extend_js', plugins_url('vc_extend.js', __FILE__), array('jquery') );
}

/*
Lets register our shortcode with bartag base and few params (attributes):
  * foo
  * color
  * content
  
  [bartag foo="something" color="#FFF"] Content here [/bartag]
  
  More information can be found here:
  http://kb.wpbakery.com/index.php?title=Visual_Composer_tutorial
*/
add_shortcode( 'bartag', 'bartag_func' );
function bartag_func( $atts, $content = null ) {
  extract( shortcode_atts( array(
    'foo' => 'something',
    'color' => '#FFF'
  ), $atts ) );
  
  $content = wpb_js_remove_wpautop($content); // fix unclosed/unwanted paragraph tags in $content
  
  return "<div style='color:{$color};' data-foo='${foo}'>{$content}</div>";
}

/*
Lets call wpb_map function to "register" our custom shortcode within Visual Composer interface.
*/
wpb_map( array(
  "name" => __("Bar tag test", 'vc_extend'),
  "base" => "bartag",
  "class" => "",
  "controls" => "full",
  "icon" => "icon-wpb-vc_extend",
  "category" => __('Content', 'js_composer'),
  //'admin_enqueue_js' => array(plugins_url('vc_extend_admin.js', __FILE__)),
  'admin_enqueue_css' => array(plugins_url('vc_extend_admin.css', __FILE__)),
  "params" => array(
    array(
      "type" => "textfield",
      "holder" => "div",
      "class" => "",
      "heading" => __("Text", 'vc_extend'),
      "param_name" => "foo",
      "value" => __("Default params value", 'vc_extend'),
      "description" => __("Description for foo param.", 'vc_extend')
    ),
    array(
      "type" => "colorpicker",
      "holder" => "div",
      "class" => "",
      "heading" => __("Text color", 'vc_extend'),
      "param_name" => "color",
      "value" => '#FF0000', //Default Red color
      "description" => __("Choose text color", 'vc_extend')
    ),
    array(
      "type" => "textarea_html",
      "holder" => "div",
      "class" => "",
      "heading" => __("Content", 'vc_extend'),
      "param_name" => "content",
      "value" => __("<p>I am test text block. Click edit button to change this text.</p>", 'vc_extend'),
      "description" => __("Enter your content.", 'vc_extend')
    )
  )
) );