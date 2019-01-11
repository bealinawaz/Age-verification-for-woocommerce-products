<?php /*
Plugin Name: Germany Age Verify
Plugin URI: http://codeconvolution.com/
description: a germany age verify plugin via germany card on woo checkouts
Version: 1.0
Author: Ali Nawaz
Author URI: http://codeconvolution.com/
License: GPL2
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define("PluginName", "Germeny Age Verify");
define("Prefix", "gav");
define("Version", 1.0);
define("post_type", "gav");
define("shortcode", "AGE_VERIFY");
define("ADMIN_EMAIL", "alinawazdi@gmail.com");

function gav_wp_head() {
	wp_enqueue_script("jquery");
} add_action("wp_head", "gav_wp_head");

function gav_init_category() {
	$term = term_exists( 'FSK18', 'product_cat' );
	if ( false == $term ) {
		wp_insert_term( 'FSK18', 'product_cat', array( 'description'=> 'A FSK18 is a age verify category.', 'slug' => 'FSK18', ) );
	}
} add_action("init", "gav_init_category");

// define the woocommerce_checkout_init callback 
function action_woocommerce_checkout_init( $self_instance ) {
	$cat_check = false;
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$product = $cart_item['data'];
		foreach($product->category_ids as $cat) {
			$get_term = get_term( $cat, "product_cat" );
			if($get_term->name == "FSK18") {
				$cat_check = true;
				break;
			}
			if($cat_check == true) { break; }
		}
		if($cat_check == true) { break; }
	}
	if ( $cat_check ) {
		add_action('woocommerce_after_order_notes', 'customise_checkout_field');
		add_action('woocommerce_checkout_create_order','my_custom_checkout_field_update_meta');
		add_action('woocommerce_after_checkout_validation', 'is_express_delivery');
	}
}; add_action( 'woocommerce_checkout_init', 'action_woocommerce_checkout_init' ); 

function customise_checkout_field($checkout) { global $current_user;
	echo '<div id="age-verify"><h2>' . __('Age Verification') . '</h2>'; ?>
		<style>
			.alert-area {}
			.alert-area .close { display:none; }
			.clearfix .age-verify-input { float: left; width: 21%; margin-right: 5%; }
			.clearfix .age-verify-input:last-child { margin-right: 0%; }
		</style>
		<div id="alert-area" class="alert-area <?php if($current_user->verify_age != 1) { echo "close"; } ?>">
			<?php if($current_user->verify_age == 1) { ?>
				<ul class="woocommerce-error"><li>Your Age Verify</li></ul>
			<?php } else { ?>
				<ul class="woocommerce-error"><li>Please Verify Your Age</li></ul>
			<?php } ?>
		</div>
		<h3>Meine Personalausweis-Nummer:</h3>
		<div class="clearfix">
			<?php woocommerce_form_field('ida', array( 'type' => 'text', 'class' => array( 'avi1 age-verify-input form-row-wide' ) , 'label' => __('ID Card A') , 'placeholder' => __('ID Card A') , 'required' => true, ) , $current_user->ida);
			woocommerce_form_field('idb', array( 'type' => 'text', 'class' => array( 'avi2 age-verify-input form-row-wide' ) , 'label' => __('ID Card B') , 'placeholder' => __('ID Card B') , 'required' => true, ) , $current_user->idb);
			woocommerce_form_field('idc', array( 'type' => 'text', 'class' => array( 'avi3 age-verify-input form-row-wide' ) , 'label' => __('ID Card C') , 'placeholder' => __('ID Card C') , 'required' => true, ) , $current_user->idc);
			woocommerce_form_field('idd', array( 'type' => 'text', 'class' => array( 'avi4 age-verify-input form-row-wide' ) , 'label' => __('ID Card D') , 'placeholder' => __('ID Card D') , 'required' => true, ) , $current_user->idd); ?>
		</div>
		<div id="verifyIDCard" class="button alt" style="padding: 0px 50px;">Verify it</div>
		<script>
			jQuery(document).ready(function(){
				jQuery("#verifyIDCard").click(function(){
					var ida = jQuery("input#ida").val();
					var idb = jQuery("input#idb").val();
					var idc = jQuery("input#idc").val();
					var idd = jQuery("input#idd").val();
					jQuery.ajax({
						method:"POST",
						url:"<?php echo plugin_dir_url( __FILE__ ) . 'ajax.php'; ?>",
						data:{action:"verifyIDCard", ida:ida, idb:idb, idc:idc, idd:idd},
						success:function(data) {
							jQuery("#alert-area").removeClass("close");
							//alert(data);
							jQuery("#alert-area").html(data);
						}
					});
				});
			});
		</script>
	<?php echo '</div>';
}

function my_custom_checkout_field_update_meta( $order ){
    if( isset($_POST['ida']) && ! empty($_POST['ida']) ) $order->update_meta_data( 'ida', sanitize_text_field( $POST['ida'] ) );
    if( isset($_POST['idb']) && ! empty($_POST['idb']) ) $order->update_meta_data( 'idb', sanitize_text_field( $POST['idb'] ) );
    if( isset($_POST['idc']) && ! empty($_POST['idc']) ) $order->update_meta_data( 'idc', sanitize_text_field( $POST['idc'] ) );
    if( isset($_POST['idd']) && ! empty($_POST['idd']) ) $order->update_meta_data( 'idd', sanitize_text_field( $POST['idd'] ) );
}

function is_express_delivery( $order_id ) {

	$order = new WC_Order( $order_id );
		
	if(empty($_POST['ida'])) { wc_add_notice( __( 'ID Card A Empty. Please put data in it', 'woocommerce' ), 'error' ); }
	if(empty($_POST['idb'])) { wc_add_notice( __( 'ID Card B Empty. Please put data in it', 'woocommerce' ), 'error' ); }
	if(empty($_POST['idc'])) { wc_add_notice( __( 'ID Card C Empty. Please put data in it', 'woocommerce' ), 'error' ); }
	if(empty($_POST['idd'])) { wc_add_notice( __( 'ID Card D Empty. Please put data in it', 'woocommerce' ), 'error' ); }

	$perso_id = $_POST['ida']." ".$_POST['idb']." ".$_POST['idc']." ".$_POST['idd'];

 	$perso_checksum = perso_checksum($perso_id);
 
	if($perso_checksum) { wc_add_notice( __( 'Your age verify.', 'woocommerce' ), 'success' ); }
	else { wc_add_notice( __( 'Your Card is not valid to verify your age.', 'woocommerce' ), 'error' ); }
}

function check_number($id, $checknumber) {
 $p = 7;
 $sum = 0;
 for($i=0; $i < strlen($id); $i++) {
 $char = $id{$i};
 
 if($char >= '0' && $char <= '9')
 $int = intval($char);
 else
 $int = ord($char)-55;
 
 $sum += $int*$p;
 
 if($p==1)
 $p=7;
 else if($p==3)
 $p=1;
 else if($p==7)
 $p=3;
 }
   
 $last_number = substr(strval($sum), -1);
 
 return $last_number == $checknumber;
}
 

 function perso_type($id) {
 $splits = explode(" ", strtoupper($id));
 if(strlen($splits[0]) == 11 && strlen($splits[1]) == 7 && strlen($splits[2]) == 7 && strlen($splits[3]) == 1) {
 return 'old';
 } else if(strlen($splits[0]) == 10 && strlen($splits[1]) == 7 && strlen($splits[2]) == 8 && strlen($splits[3]) == 1) {
 return 'new';
 } else {
 return 'unknown';
 } 
}
 

 function perso_checksum($id) {
 $splits = explode(" ", strtoupper($id));
 
 $checksums = array();
 $perso_type = perso_type($id);
 
 if($perso_type == 'unknown') {
 return false;
 }
 
 $checksums[] = array(substr($splits[0],0,9), substr($splits[0],9,1));
 $checksums[] = array(substr($splits[1],0,6), substr($splits[1],6,1));
 $checksums[] = array(substr($splits[2],0,6), substr($splits[2],6,1));
 $checksums[] = array(substr($splits[0],0,10).substr($splits[1],0,7).substr($splits[2],0,7), $splits[3]); 
 
 
 foreach($checksums as $checksum) {
 if(!check_number($checksum[0], $checksum[1])) {
 return false;
 }
 }
 
   return true;
}
 
function perso_gueltig($id) {
   $splits = explode(" ", $id);
   
   $valid_until = mktime(0,0,0, substr($splits[2], 2, 2) , substr($splits[2], 4, 2) , "20".substr($splits[2], 0, 2));
 
   if(time() > $valid_until)
      return false;
 
   return true;
}
 
function perso_info($id) {
   $splits = explode(" ", $id);
 
   $return = new stdClass();
   $return->perso_type = perso_type($id);
   $return->geb = new stdClass();
   $return->geb->tag= $splits[1]{4} . $splits[1]{5}; 
   $return->geb->monat = $splits[1]{2} . $splits[1]{3}; 
   $return->geb->jahr = $splits[1]{0} . $splits[1]{1}; 
   if($return->geb->jahr > intval(date("y"))) {
 $return->geb->jahr = "19".$return->geb->jahr;
   } else {
 $return->geb->jahr = "20".$return->geb->jahr;
   }
 
 
   $alter = date("Y") - $return->geb->jahr;
 
 if( (date("n") < $return->geb->monat) OR (date("n") == $return->geb->monat AND date("j") < $return->geb->tag) ) {
 $alter--;
 }
 
   $return->alter = $alter;
 
   if($alter >= 18) {
      $return->volljaehrig = true;
   } else {
      $return->volljaehrig = false;
   }
   
   $return->ablauf = new stdClass();
   $return->ablauf->tag = $splits[2]{4} . $splits[2]{5}; 
   $return->ablauf->monat = $splits[2]{2} . $splits[2]{3}; 
   $return->ablauf->jahr = "20".$splits[2]{0} . $splits[2]{1}; 
 
   if($return->perso_type == 'old') {
 $return->herkunft = $splits[0]{10};
   } else {
        $return->herkunft = $splits[2]{7};
   }
   if(strtolower($return->herkunft) == "d") {
      $return->deutscher = true;
   } else {
      $return->deutscher = false;
   }
   $return->behoerdenkennzahl = substr($splits[0], 0, 4);
 
 
   return $return;
} ?>