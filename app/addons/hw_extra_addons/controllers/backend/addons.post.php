<?php

/**
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
use Tygh\Addons\SchemesManager;
use Tygh\Themes\Themes;

if ($mode == 'delete' || $mode == 'export' || $mode == 'uninstall_install') {

	$addon = $_REQUEST['addon'];

	#check if add-on folder exists
	if(!is_dir(Registry::get('config.dir.addons').$addon)){
	   	fn_set_notification('W', __('warning'), __('addon_is_missing'));
	    return array(CONTROLLER_STATUS_REDIRECT, "addons.manage");
	}
}

#DELETE ADD-ON
if ($mode == 'delete') {

	$addon_settings = Registry::get('addons.'.$addon);
	if(!empty($addon_settings)){
	   	fn_set_notification('W', __('warning'), __('addon_is_active'));
	    return array(CONTROLLER_STATUS_REDIRECT, "addons.manage");
	}

	#CREATE ADD-ON FOLDERS LIST
	$dirs = array(
		#APP
		Registry::get('config.dir.addons').$addon,

		#BACKEND
		Registry::get('config.dir.design_backend').'css/addons/'.$addon,
		Registry::get('config.dir.design_backend').'mail/media/images/addons/'.$addon,
		Registry::get('config.dir.design_backend').'mail/templates/addons/'.$addon,
		Registry::get('config.dir.design_backend').'media/images/addons/'.$addon,
		Registry::get('config.dir.design_backend').'media/fonts/addons/'.$addon,
		Registry::get('config.dir.design_backend').'templates/addons/'.$addon,

		#JS
		Registry::get('config.dir.root').'/js/addons/'.$addon
	);

   	$available_themes = fn_get_available_themes(Registry::get('settings.theme_name'));
   	foreach ($available_themes['installed'] as $theme => $value) {
		#FRONTEND
		$dirs[] = Registry::get('config.dir.design_frontend').$theme.'/css/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.design_frontend').$theme.'/mail/media/images/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.design_frontend').$theme.'/mail/templates/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.design_frontend').$theme.'/media/images/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.design_frontend').$theme.'/media/fonts/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.design_frontend').$theme.'/templates/addons/'.$addon;
   	}

	foreach ($available_themes['repo'] as $theme => $value) {
		#THEMES REPOSITORY
		$dirs[] = Registry::get('config.dir.themes_repository').$theme.'/css/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.themes_repository').$theme.'/mail/media/images/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.themes_repository').$theme.'/mail/templates/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.themes_repository').$theme.'/media/images/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.themes_repository').$theme.'/media/fonts/addons/'.$addon;
		$dirs[] = Registry::get('config.dir.themes_repository').$theme.'/templates/addons/'.$addon;
	}

	#LANGS
	$langs = fn_get_dir_contents(Registry::get('config.dir.lang_packs'));
	foreach ($langs as $lang) {
		$dirs[] = Registry::get('config.dir.lang_packs').$lang.'/addons/'.$addon.'.po';
	}

 	#CACHE
	$dirs[] = Registry::get('config.dir.cache');

   	#DELETE ADD-ON FOLDERS
   	foreach ($dirs as $dir) {
		fn_rm($dir);
   	}

   	fn_set_notification('N', __('notice'), __('hw_extra_addons_deleted'));
    return array(CONTROLLER_STATUS_OK, "addons.manage");
}

#EXPORT ADD-ON
if ($mode == 'export') {

	$addon_scheme = SchemesManager::getScheme($addon);
	$addon_version = $addon_scheme->getVersion();

	$export_path = Registry::get('config.dir.cache_misc').'tmp/export';
	$temp_path = $export_path.'/'.$addon.'/';

	#RE-CREATE SOURCE FOLDER
	fn_rm($temp_path);
	fn_mkdir($temp_path);

	#APP
	fn_copy(
		Registry::get('config.dir.addons').$addon,
		$temp_path.'app/addons/'.$addon.'/'
	);

	#JS
	if(is_dir(Registry::get('config.dir.root').'/js/addons/'.$addon)){
		fn_copy(
			Registry::get('config.dir.root').'/js/addons/'.$addon,
			$temp_path.'js/addons/'.$addon.'/'
		);
	}

	#THEME DIRS
	$theme_dirs = array(
		'css/addons/'.$addon,
		'mail/media/images/addons/'.$addon,
		'mail/templates/addons/'.$addon,
		'media/images/addons/'.$addon,
		'media/fonts/addons/'.$addon,
		'templates/addons/'.$addon
	);

	#BACKEND
	foreach ($theme_dirs as $dir) {
		if(!is_dir(Registry::get('config.dir.design_backend').$dir)) continue;
		fn_copy(
			Registry::get('config.dir.design_backend').$dir,
			$temp_path.'design/backend/'.$dir
		);
	}

	#ACTIVE THEME NAME
	$theme_name = Registry::get('settings.theme_name');

    $theme = Themes::factory($theme_name);
    $theme_manifest = $theme->getManifest();

    #PARENT THEME FIRST
    $parent_theme = !empty($theme_manifest['parent_theme'])?$theme_manifest['parent_theme']:'';
    if(!empty($parent_theme) && $parent_theme!=$theme_name){
		foreach ($theme_dirs as $dir) {
			if(!is_dir(Registry::get('config.dir.design_frontend').$parent_theme.'/'.$dir)) continue;
			fn_copy(
				Registry::get('config.dir.design_frontend').$parent_theme.'/'.$dir,
				$temp_path.'var/themes_repository/'.$parent_theme.'/'.$dir
			);
		}
    }
    
	#ACTIVE THEME
	foreach ($theme_dirs as $dir) {
		$dir_fullpath = Registry::get('config.dir.design_frontend').$theme_name.'/'.$dir;
		$theme_folder = !empty($parent_theme)?$parent_theme:$theme_name;
		if(!is_dir($dir_fullpath)) continue;
		fn_copy(
			$dir_fullpath,
			$temp_path.'var/themes_repository/'.$theme_folder.'/'.$dir
		);
	}

	#LANGS
	$langs = fn_get_dir_contents(Registry::get('config.dir.lang_packs'));
	foreach ($langs as $lang) {
		if(file_exists(Registry::get('config.dir.lang_packs').$lang.'/addons/'.$addon.'.po')){
			fn_put_contents(
				$temp_path.'var/langs/'.$lang.'/addons/'.$addon.'.po',
				fn_get_contents(Registry::get('config.dir.lang_packs').$lang.'/addons/'.$addon.'.po')
			);
		}
	}

	$supplier = $addon_scheme->getSupplier();
	$filename = $addon.'-addon-v'.$addon_version.'-for-'.PRODUCT_NAME.'-'.PRODUCT_VERSION.((!empty($supplier))?'-by-'.$supplier:'').'.zip';
	$filename = strtolower($filename);

	#COMPRESS FOLDER AND DOWNLOAD
	fn_compress_files($filename, $addon, dirname($temp_path));
	fn_get_file($export_path.'/'.$filename, $filename, true);

	#CLEANUP
	fn_rm($temp_path);
	fn_rm($export_path.'/'.$filename);

	exit;
}

#UNINSTALL & INSTALL
if ($mode == 'uninstall_install') {
	fn_uninstall_addon($addon);
	fn_install_addon($addon);
    return array(CONTROLLER_STATUS_OK, "addons.manage");
}
