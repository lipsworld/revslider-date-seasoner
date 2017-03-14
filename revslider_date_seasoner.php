<?php
/*
Plugin Name: Slider Revolution Date Seasoner
Plugin URI: https://kalligo.se
Description: Add Slider Revolution Date Seasoner functionality to VC
Version: 0.1
Author: Thim K
Text Domain: revslider-date-seasoner
Author URI: https://kalligo.se
License: GPLv2 or later
*/


// don't load directly
if (!defined('ABSPATH')) die('-1');

class VCExtendAddonClass {
    function __construct() {
        // We safely integrate with VC with this hook
        add_action( 'init', array( $this, 'integrateWithVC' ) );
 
        // Use this when creating a shortcode addon
        add_shortcode( 'revslider_seasoner', array( $this, 'renderRevSliderSeasoner' ) );

        // Register CSS and JS
        add_action( 'wp_enqueue_scripts', array( $this, 'loadCssAndJs' ) );

        vc_add_shortcode_param( 'date', array( $this, 'dateSettingsField' ) );
    }

    /**
     * Add custom param, date html5 text field
     */
    public function dateSettingsField( $settings, $value ) {
       return '<div class="my_param_block">'
         .'<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value wpb-textinput ' .
         esc_attr( $settings['param_name'] ) . ' ' .
         esc_attr( $settings['type'] ) . '_field" type="date" value="' . esc_attr( $value ) . '" />' .
         '</div>';
    }
 
    public function integrateWithVC() {
        // Check if Visual Composer is installed
        if ( ! defined( 'WPB_VC_VERSION' ) ) {
            // Display notice that Visual Compser is required
            add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
            return;
        }
       
        /**
         * Get RevSlider slides
         */
        if ( class_exists( 'RevSlider' ) ) {
          $rev_slider = new RevSlider();
          $sliders = $rev_slider->getAllSliderAliases();
          array_unshift($sliders, '');
        } else {
          $sliders = array();
        }


        vc_map( array(
            "name" => __("Revolution Slider Date Seasoner", 'revslider-date-seasoner'),
            "description" => __("Add slider to swith based on different seasons.", 'revslider-date-seasoner'),
            "base" => "revslider_seasoner",
            "class" => "",
            "controls" => "full",
            "icon" => plugins_url('assets/revslider-seasoner-icon.png', __FILE__), // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
            "category" => __('Content', 'js_composer'),
            "params" => array(
                array(
                  'type' => 'param_group',
                  'heading' => __( 'Sliders And Dates To Show', 'js_composer' ),
                  'param_name' => 'sliders',
                  'show_settings_on_create' => true,
                  'value' => urlencode( json_encode( array(
                    array(
                      'title' => __( 'Spring', 'js_composer' ),
                      "value" => $sliders,
                      'from_date' => date('Y-03-01'),
                      'to_date' => date('Y-m-t', strtotime(date('Y-05-01'))),
                    ),
                    array(
                      'title' => __( 'Summer', 'js_composer' ),
                      "slider" => $sliders,
                      'from_date' => date('Y-06-01'),
                      'to_date' => date('Y-m-t', strtotime(date('Y-08-01'))),
                    ),
                    array(
                      'title' => __( 'Fall', 'js_composer' ),
                      "slider" => $sliders,
                      'from_date' => date('Y-09-01'),
                      'to_date' => date('Y-m-t', strtotime(date('Y-11-01'))),
                    ),
                    array(
                      'title' => __( 'Winter', 'js_composer' ),
                      "slider" => $sliders,
                      'from_date' => date('Y-12-01'),
                      'to_date' => date('Y-m-t', strtotime(date('Y-02-01'))),
                    ),
                  ) ) ),
                  'params' => array(
                    array(
                      'type' => 'textfield',
                      'heading' => __( 'Title', 'js_composer' ),
                      'param_name' => 'title',
                      'description' => __( 'Enter title for slider date.', 'js_composer' ),
                      'admin_label' => true,
                    ),
                    array(
                      "type" => "date",
                      "holder" => "div",
                      "class" => "",
                      "heading" => __("From Date", 'revslider-date-seasoner'),
                      "param_name" => "from_date",
                      "value" => date('Y-m-d'),
                      "edit_field_class" => "vc_col-sm-6",
                      'description' => __( 'Choose from and to date to show slider on.', 'js_composer' ),
                    ),
                    array(
                      "type" => "date",
                      "holder" => "div",
                      "class" => "",
                      "heading" => __("To Date", 'revslider-date-seasoner'),
                      "param_name" => "to_date",
                      "value" => date('Y-m-d'),
                      "edit_field_class" => "vc_col-sm-6",
                    ),
                    array(
                      "type" => "dropdown",
                      "holder" => "div",
                      "heading" => __("Choose A Slider", 'revslider-date-seasoner'),
                      "param_name" => "slider",
                      "value" => $sliders,
                      "edit_field_class" => "vc_col-sm-12",
                    ),
                ),
            ),
          )
        ) );
    }
    
    /**
     * Shortcode logic how it should be rendered
     */
    public function renderRevSliderSeasoner( $atts, $content = null ) {

      extract( shortcode_atts( array(
        'sliders' => '',
      ), $atts ) );
      $content = wpb_js_remove_wpautop($content, true); // fix unclosed/unwanted paragraph tags in $content

      $sliders = vc_param_group_parse_atts($atts['sliders']); // Gets all the group items to run through them in a loop

      $output = '<div class="error text-center">'.__('No Slider Could Be Found.').'</div>';
      $curr_date = date('Y-m-d');
      foreach ($sliders as $slider) {
        if($slider['from_date'] >= $curr_date && $slider['to_date'] <= $curr_date) { // Check if the slider is inside the current date interval, then show it and stop the loop.
          $output = do_shortcode( '[rev_slider alias="'.$slider['slider'].'"]', false );
          break;
        }
      }
      
      return $output;
    }

    /**
     * Load plugin css and javascript files which you may need on front end of your site
     */
    public function loadCssAndJs() {
      wp_register_style( 'vc_extend_style', plugins_url('assets/vc_extend.css', __FILE__) );
      wp_enqueue_style( 'vc_extend_style' );
    }

    /**
     * Show notice if Visual Composer is not activated
     */
    public function showVcVersionNotice() {
        $plugin_data = get_plugin_data(__FILE__);
        echo '
        <div class="updated">
          <p>'.sprintf(__('<strong>%s</strong> requires <strong><a href="http://bit.ly/vcomposer" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'revslider-date-seasoner'), $plugin_data['Name']).'</p>
        </div>';
    }
}
// Finally initialize code
new VCExtendAddonClass();