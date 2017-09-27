<?php 
/**
 * Plugin Name: WooCommerce Checkout Age Verification
 * Description: This plugin is used to varify age of user at WooCommerce checkout. 
 * Version: 1.1
 * Author: Parth Shah, lukecav
 * Author URI: https://github.com/lukecav/
 * Plugin URI: https://github.com/lukecav/woocommerce-checkout-age-verification
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-registration-redirect
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.0
 */

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Put your plugin code here

add_action( 'wp_enqueue_scripts', 'wcav_custom_enqueue_datepicker' );
 
function wcav_custom_enqueue_datepicker() {
	// Optional - enqueue styles
	wp_enqueue_script('jquery-ui-datepicker');
	
	wp_register_style('datepicker_css', plugins_url('css/jquery-ui.css',__FILE__ ));
	wp_enqueue_style('datepicker_css');

}


//Adding Field
function wcav_my_custom_checkout_field( $checkout ) {

$checkout = WC()->checkout();
 
    echo '<div id="my_custom_checkout_field"><h3>' . __('Age Verification') . '</h3>';
 
    woocommerce_form_field( 'my_field_name', array(
        'type'          => 'text',
	'required'      => true,
	'readonly'      => 'readonly',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('Your Birthdate (mm-dd-yyyy)'),
        'placeholder'   => __(''),
		'id'=>__('MyDate')
        ), $checkout->get_value( 'my_field_name' ));
 
    echo '</div>';
	{?>
	
	


	<script>
 jQuery(document).ready(function() {
    jQuery('#MyDate').datepicker({
        dateFormat : 'mm-dd-yy',
		changeMonth: true,
      changeYear: true,
	  yearRange: '1900:2015',
    });
	
	
});


</script>
	<?php }
	
}
add_action( 'woocommerce_checkout_before_customer_details' ,'wcav_my_custom_checkout_field' );




//validating


add_action('woocommerce_checkout_process', 'wcav_my_custom_checkout_field_process');
 
function wcav_my_custom_checkout_field_process() {
    // Check if set, if its not set add an error.
	
	$age=$_POST['my_field_name'];
	$y = explode("-", $age);
	
$month=$y['0']; // user' age
$day=$y['1'];
$year=$y['2'];

// get diffrence with cureent date
 $year_diff  = date("Y") - $year;
 $month_diff = date("m") - $month;
 $day_diff   = date("d") - $day;
 
 //logic
 
	$order_number_start = get_option( 'woocommerce_order_number_start', 1 );
	

 if($year_diff == $order_number_start )
 {
 
	if ( $month_diff == 0)
	{
		if($day_diff >0)
		{
		
		$year_diff++; 
			
		}
		
	}
 
 }

 
 
 


	
	$final_age=$year_diff;
	
	
	
	
	
	
    if ( ! $_POST['my_field_name'] || $final_age < $order_number_start ||  $final_age == $order_number_start)
	
	{

	$order_number_start = get_option( 'woocommerce_order_number_start', 1 );
	
        wc_add_notice( __( 'Your age must be above '.$order_number_start.' to complete this order.' ), 'error' );
}}

// Saving data

add_action( 'woocommerce_checkout_update_order_meta', 'wcav_my_custom_checkout_field_update_order_meta' );
 
function wcav_my_custom_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['my_field_name'] ) ) {
        update_post_meta( $order_id, 'My Field', sanitize_text_field( $_POST['my_field_name'] ) );
    }
}

//showing data in admin

add_action( 'woocommerce_admin_order_data_after_billing_address', 'wcav_my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function wcav_my_custom_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Birthdate').':</strong> ' . get_post_meta( $order->id, 'My Field', true ) . '</p>';
}

add_filter( 'woocommerce_general_settings', 'wcav_add_order_number_start_setting' );



function wcav_add_order_number_start_setting( $settings ) {



  $updated_settings = array();



  foreach ( $settings as $section ) {



    // at the bottom of the General Options section

    if ( isset( $section['id'] ) && 'general_options' == $section['id'] &&

       isset( $section['type'] ) && 'sectionend' == $section['type'] ) {



      $updated_settings[] = array(

        'name'     => __( 'Age Limit', 'wc_seq_order_numbers' ),

        'desc_tip' => __( 'Set Age limit to make order.', 'wc_seq_order_numbers' ),

        'id'       => 'woocommerce_order_number_start',

        'type'     => 'text',

        'css'      => 'min-width:300px;',

        'std'      => '1',  // WC < 2.0

        'default'  => '18',  // WC >= 2.0

        'desc'     => __( '', 'wc_seq_order_numbers' ),

      );

    }



    $updated_settings[] = $section;

  }

  return $updated_settings;

}}?>
