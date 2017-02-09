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
use Tygh\Addons\SchemesManager;

if ($mode == 'delete') {
	#check if disable
	$addon = $_REQUEST['addon'];

	$dir = array(
		Registry::get('config.dir.addons').$addon,
		#backend
		Registry::get('config.dir.design_backend').'css/addons/'.$addon,
		Registry::get('config.dir.design_backend').'mail/media/images/addons/'.$addon,
		Registry::get('config.dir.design_backend').'mail/templates/addons/'.$addon,
		Registry::get('config.dir.design_backend').'media/images/addons/'.$addon,
		Registry::get('config.dir.design_backend').'media/fonts/addons/'.$addon,
		Registry::get('config.dir.design_backend').'templates/addons/'.$addon,
		#js
		Registry::get('config.dir.root').'/js/addons/'.$addon
	);

   	$available_themes = fn_get_available_themes(Registry::get('settings.theme_name'));
   	foreach ($available_themes['installed'] as $theme => $value) {
		#frontend
		$dir[] = Registry::get('config.dir.design_frontend').$theme.'/css/addons/'.$addon;
		$dir[] = Registry::get('config.dir.design_frontend').$theme.'/mail/media/images/addons/'.$addon;
		$dir[] = Registry::get('config.dir.design_frontend').$theme.'/mail/templates/addons/'.$addon;
		$dir[] = Registry::get('config.dir.design_frontend').$theme.'/media/images/addons/'.$addon;
		$dir[] = Registry::get('config.dir.design_frontend').$theme.'/media/fonts/addons/'.$addon;
		$dir[] = Registry::get('config.dir.design_frontend').$theme.'/templates/addons/'.$addon;	
   	}

   	foreach ($available_themes['repo'] as $theme => $value) {
		#frontend
		$dir[] = Registry::get('config.dir.themes_repository').$theme.'/css/addons/'.$addon;
		$dir[] = Registry::get('config.dir.themes_repository').$theme.'/mail/media/images/addons/'.$addon;
		$dir[] = Registry::get('config.dir.themes_repository').$theme.'/mail/templates/addons/'.$addon;
		$dir[] = Registry::get('config.dir.themes_repository').$theme.'/media/images/addons/'.$addon;
		$dir[] = Registry::get('config.dir.themes_repository').$theme.'/media/fonts/addons/'.$addon;
		$dir[] = Registry::get('config.dir.themes_repository').$theme.'/templates/addons/'.$addon;	
   	} 

   	#clear cache
   	$dir[] = Registry::get('config.dir.cache');

   	foreach ($dir as $value) {
		fn_rm($value);
   	}

   	fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('hw_extra_addons_deleted'));
    	return array(CONTROLLER_STATUS_OK, "addons.manage");   	
}

if ($mode == 'export') {

	#check if disable
	$addon = $_REQUEST['addon'];

	//$addon_scheme = SchemesManager::getScheme($addon);
    	//if ($addon_scheme != false) { exit; }

	$dir = array();

	//check dir
	if(!is_dir(Registry::get('config.dir.addons').$addon)){ echo'ddddd'; exit; }

	//check version
              $export_path = Registry::get('config.dir.cache_misc') . 'tmp/export';
              $temp_path = $export_path.'/'.$addon.'/';

              // Re-create source folder
              fn_rm($temp_path);
              fn_mkdir($temp_path);

	#APP
              fn_copy(
              		Registry::get('config.dir.addons').$addon,
              		$temp_path.'app/addons/'.$addon.'/'
              	);

	#JS
              fn_copy(
              		Registry::get('config.dir.root').'/js/addons/'.$addon,
              		$temp_path.'js/addons/'.$addon.'/'
              	);


              $dirs = array(
		'css/addons/'.$addon,
		'mail/media/images/addons/'.$addon,
		'mail/templates/addons/'.$addon,
		'media/images/addons/'.$addon,
		'media/fonts/addons/'.$addon,
		'templates/addons/'.$addon		
	);

	#backend
              foreach ($dirs as $dir) {
              	              if(!is_dir(Registry::get('config.dir.design_backend').$dir)) continue;
	              fn_copy(
	              		Registry::get('config.dir.design_backend').$dir,
	              		$temp_path.'design/backend/'.$dir
	              	);
              }


	//current theme
	$theme = Registry::get('settings.theme_name');

	#frontend
              foreach ($dirs as $dir) {
              		if(!is_dir(Registry::get('config.dir.design_frontend').$theme.'/'.$dir)) continue;
	              fn_copy(
	              		Registry::get('config.dir.design_frontend').$theme.'/'.$dir,
	              		$temp_path.'var/themes_repository/'.$theme.'/'.$dir
	              	);
              }

              //lang
	if(file_exists(Registry::get('config.dir.lang_packs').'en/addons/'.$addon.'.po')){
		fn_put_contents(
			$temp_path.'var/langs/en/addons/'.$addon.'.po',
			fn_get_contents(Registry::get('config.dir.lang_packs').'en/addons/'.$addon.'.po')
		);
	}
	

	$filename = $addon . '-cs-cart-addon.zip';
	//$filename = $addon . '-cs-cart-4.2.x-addon-1.0.zip';

	fn_compress_files($filename, $addon, dirname($temp_path));
               fn_rm($temp_path);
               fn_get_file($export_path.'/'.$filename, $filename, true);

	exit;
}

if ($mode == 'uninstall_install') {
    	fn_uninstall_addon($_REQUEST['addon']);
	fn_install_addon($_REQUEST['addon']);
    	return array(CONTROLLER_STATUS_OK, "addons.manage");
}