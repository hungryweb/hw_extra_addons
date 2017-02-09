<?php

/**
 *
 * Thank you for your purchase! You are the best!
 * 
 * @copyright    2017 Hungryweb
 * @website      https://www.hungryweb.net/
 * @support      support@hungryweb.net
 * @license      https://www.hungryweb.net/license-agreement.html
 *
 * --------------------------------------------------------------------------------- 
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree.
 * ---------------------------------------------------------------------------------
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
		Http::post('https://www.hwebcs.com/ws/addons', $request);
	}
}