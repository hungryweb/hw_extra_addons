<?php
/*
 * © 2015 Hungryweb
 * 
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  
 * IN  THE "HW-LICENSE.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE. 
 * 
 * @website: www.hungryweb.net
 * @support: support@hungryweb.net
 *  
 */

if ( !defined('BOOTSTRAP') ) { die('Access denied'); }

use Tygh\Registry;
use Tygh\Http;




#HW Action
function fn_hw_extra_addons_install(){ fn_hw_action('extra_addons','install'); }
function fn_hw_extra_addons_uninstall(){ fn_hw_action('extra_addons','uninstall'); }
if (!function_exists('fn_hw_action')){
	function fn_hw_action($addon,$a){
		$request = array(	
			'addon' => $addon,
			'host' => Registry::get('config.http_host'),
			'path' => Registry::get('config.http_path'),
			'version' => PRODUCT_VERSION,
			'edition' => PRODUCT_EDITION,
			'lang' => strtoupper(CART_LANGUAGE),
			'a' => $a
		);
		Http::post('http://api.hungryweb.net/', $request);
	}
}