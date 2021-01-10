<?php

class WCD {

    public $wcd_discount_logged = 0;

    public $wcd_discount_onclose = 0;

    public $wcd_discount_rate = 0;

    public function __construct() {

        $this->wcd_discount_logged = get_option( 'wcd_discount_logged' );
        $this->wcd_discount_onclose = get_option( 'wcd_discount_onclose' );
        $this->init();

    }

    public function init() {

        add_action( 'admin_menu', function (){
            add_submenu_page( 'woocommerce', 'Discounts', 'Discounts', 'manage_options', 'discounts', array( $this, 'plugin_setting_page' ) );
        } );

        add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );

        add_filter( 'woocommerce_product_get_price', array( $this, 'logged_discount_price' ), 10, 2 );
        add_filter( 'woocommerce_product_variation_get_price', array( $this, 'logged_discount_price' ), 10, 2 );
        add_filter( 'woocommerce_variable_price_html', array( $this, 'logged_discount_variable_price_show' ), 10, 2 );

        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );
        add_action( 'wp_footer', array( $this, 'onclose_modal' ) );

    }

    public function register_scripts_and_styles() {
        wp_enqueue_style( 'wcd-styles-css', plugins_url( 'assets/css/styles.css' , dirname(__FILE__) ) );
        wp_enqueue_script( 'wcd-scripts-js', plugins_url( 'assets/js/scripts.js' , dirname(__FILE__) ), array(), '1.0.0', true );
    }

    public function register_plugin_settings() {
        register_setting( 'wcd_settings', 'wcd_discount_logged', 'intval' );
        register_setting( 'wcd_settings', 'wcd_discount_onclose', 'intval' );
    }

    public function plugin_setting_page() {
        ?>
        <div class="wrap">
            <h2><?php _e( 'WooCommerce Discounts', 'wcd' ); ?></h2>
            <form method="post" action="options.php">
                <?php settings_fields( 'wcd_settings' ); ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="wcd_discount_logged"><?php _e( 'Discount for logged users, %', 'wcd' ); ?></label></th>
                            <td><input type="number" min="0" max="100" name="wcd_discount_logged" value="<?php echo get_option( 'wcd_discount_logged' ); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="wcd_discount_onclose"><?php _e( 'Discount when user left, %', 'wcd' ); ?></label></th>
                            <td><input type="number" min="0" max="100" name="wcd_discount_onclose" value="<?php echo get_option( 'wcd_discount_onclose' ); ?>" /></td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    public function get_discount() {
        $discount = $this->wcd_discount_rate;
        if( $this->is_logged() ) {
           $discount = ( 100 - $this->wcd_discount_logged ) / 100;
        } elseif ( $this->is_has_discount() ) {
           $discount = ( 100 - $this->wcd_discount_onclose ) / 100;
        }
        return $discount;
    }

    public function is_logged() {
        return is_user_logged_in();
    }

     public function is_has_discount() {
        return $_COOKIE['wcd_get_discount'];
    }

    public function logged_discount_price( $price, $product ) {

        if ( ( $discount = $this->get_discount() ) != 0 ) {

            if ( $product->is_on_sale() && ! ( $product->get_type() == 'variable' || $product->get_type() == 'grouped' ) ) {

                return min( $price, ( $product->get_regular_price() * $discount ) );

            } else {

                return $price * $discount;

            }
        }
        return $price;
    }

    public function logged_discount_variable_price_show( $price, $product ) {

        if ( ( $discount = $this->get_discount() ) != 0 ) {

            $variation_prices = $product->get_variation_prices( true );

            $min = max( $variation_prices['price'] ) * $discount;

            // Checking for each price what smaller - sale or user discounted price
            foreach ( $variation_prices['price'] as $id=>$price ) {

                $reg_disc = $variation_prices['regular_price'][$id]  * $discount;
                $price = (float) $price;

                if ( $reg_disc > $price ) {
                    $current = $price;
                } else {
                    $current = $reg_disc;
                }

                if ( $current < $min ) {
                    $min = $current;
                }

            }

            $max = max( $variation_prices['price'] ) * $discount;

            // Check if min and max prices same
            if ( $min == $max ) {
                return wc_price( $min );
            } else {
                return wc_price( $min ) . ' - ' . wc_price( $max );
            }
        }
        return $price;

    }

    public function onclose_modal() {
        $html = '';
        if ( $this->wcd_discount_onclose != 0 && $this->is_logged() == false ) {
            $html = '
            <div id="popup" class="exitblock">
                <div class="fon"></div>
                <div class="modaltext">
                    <h1 class="popup__title">' . __( 'Stop!', 'wcd' ) . '</h1>
                    <p>' . sprintf( __( 'Stay on site and get %s&#37; discount!', 'wcd' ), $this->wcd_discount_onclose ) . '</p>
                    <div class="modalbutton">' . __( 'Get discount', 'wcd' ) . '</div>
                    <div class="closeblock">+</div>
                </div>
            </div>';
        }
         echo $html;
    }

}