<?php
/*
Plugin Name:    CP Currency Calculator
Plugin URI:     https://www.bezahlenmitkrypto.de/krypto-tools/cryptoprice/wordpress-plugin-cp-currency-calculator/
Description:    Shortcodes to render crypto currency exchange rates and an crypto currency exchange rates calculator
Version:        1.0.2
Author:         LAMP solutions GmbH
Author URI:     https://www.lamp-solutions.de/
License:        GPLv2
Text Domain:    cp-currency-calculator
Domain Path:    /languages/
*/

//[cpcc-calculator]
function cpcc_shortcode_calculator( $atts ){
    return <<<HTML

<style type="text/css">
.cpcc-select { min-width: 80px; }
</style>
<form>
    <select id="cpcc-cc-from" name="from" class="cpcc-select">
        <option value="USD">USD</option>
        <option value="EUR">EUR</option>
    </select>
    <input id="cpcc-cc-amount" name="amount" type="text" value="1,00" autocomplete="off" />
    
    <br/>
    
    <select id="cpcc-cc-to" name="to" class="cpcc-select">
        <option value="BTC">BTC</option>
        <option value="BCH">BCH</option>
        <option value="LTC">LTC</option>
        <option value="DASH">DASH</option>
        <option value="ETH">ETH</option>
    </select>
    
    <input id="cpcc-cc-res" name="amount" type="text" value="" readonly="readonly" />
</form>

HTML;


}

function cpcc_shortcode_currency( $atts ){
    $from=$atts['from'];
    $to=$atts['to'];
    $amount=$atts['amount'];
    return "<i style=\"all:unset;\" class=\"cpcc-convert\" data-from=\"$from\" data-to=\"$to\" data-amount=\"$amount\"></i>";
}

function cpcc_script() {
    echo <<<HTML

<script type="text/javascript">
jQuery(function() {
    function cpcc_handle_conversion() {
        var current_amount = jQuery('#cpcc-cc-amount').val().replace(',', '.');
        console.log(current_amount);
          jQuery.ajax({
              cache: false,
              type: "POST",
              url: 'https://cryptoprice.api.cryptopanel.de/api/v1/calculate-exchange',
              data: { 
                  amount: current_amount,
                  origin: jQuery('#cpcc-cc-from').val(),
                  destination: jQuery('#cpcc-cc-to').val(),
              },
              success: function(e, d) {
                  if(e && e.amount) {
                      jQuery('#cpcc-cc-res').val(e.amount.replace('.', ','));
                  }
              },
              error: function(e, d) {
                  jQuery('#cpcc-cc-res').val('Error occured, please try again');
              },
            });
    }
    
    jQuery('#cpcc-cc-from').on('change', cpcc_handle_conversion);
    jQuery('#cpcc-cc-to').on('change', cpcc_handle_conversion);
    jQuery('#cpcc-cc-amount').on('keyup', cpcc_handle_conversion);
    jQuery('#cpcc-cc-amount').trigger('keyup');
    jQuery('.cpcc-convert').each(function(i,e) {
        jQuery.ajax({
          cache: false,
          type: "POST",
          url: 'https://cryptoprice.api.cryptopanel.de/api/v1/calculate-exchange',
          data: { 
              amount: jQuery(e).data('amount').replace(',', '.'),
              origin: jQuery(e).data('from'),
              destination: jQuery(e).data('to')
          },
          success: function(v, d) {
              if(v && v.amount) {
                  jQuery(e).text(v.amount)
              }
          },
          error: function(v, d) {
              jQuery(e).text('Error occured, please try again');
          }
        });
    });
});
</script>
HTML;

}

add_shortcode( 'cpcc-currency', 'cpcc_shortcode_currency' );
add_shortcode( 'cpcc-calculator', 'cpcc_shortcode_calculator' );
add_action('wp_footer', 'cpcc_script', 999);
