<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

use WHMCS\Database\Capsule;

class Multibrand {
    public static function getBrandData($id) {
        
        $mb1x = Capsule::table('tbladdonmodules')->where('module', 'MultibrandFunctionality')->first();
        $mb2x = Capsule::table('tbladdonmodules')->where('module', 'Multibrand')->first();
        
        $configuration = Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->first();
        $systemURL = $configuration->value;
        
        if(function_exists('MultibrandFunctionalityAutoLoader') && !empty($mb1x)) {
            
            $brandData = array();
            MultibrandFunctionalityAutoLoader();
            $check  = \MultibrandFunctionality\app\models\BrandRelations::factory()->searchFor('service', $id);            
            
            if(isset($check->brand_id))
            {
                $currentBrand = \MultibrandFunctionality\app\models\Brand::factory($check->brand_id)->fetchOne();
            }            
            //if not assigned to any of the brands.
            if(empty($currentBrand))
            {   
                $currentBrand = \MultibrandFunctionality\app\models\Brand::factory()->getDefaultOne();
            }
            
            $brandData['systemURL'] = $currentBrand->systemURL;
            $brandData['email'] = $currentBrand->email;
            $brandData['companyName'] = $currentBrand->company;            
            $brandData['domain'] = $currentBrand->url;
            $brandData['LogoURL'] = $currentBrand->systemURL.'/modules/addons/MultibrandFunctionality/storage/logos/'.$currentBrand->logo;
            $brandData['LogoImage'] = '<img src="'.$currentBrand->systemURL.'/modules/addons/MultibrandFunctionality/storage/logos/'.$currentBrand->logo.'"/>';
            $brandData['whmcsURL'] = $currentBrand->url;
            $brandData['whmcsLink'] = '<a href="'.$currentBrand->url.'">'.$currentBrand->url.'</a>';
            $brandData['signature'] = $currentBrand->signature;
            
            return $brandData;
        } elseif (file_exists(dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.'Multibrand'.DIRECTORY_SEPARATOR.'Loader.php') && !empty($mb2x)) {
            
            require_once dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.'Multibrand'.DIRECTORY_SEPARATOR.'Loader.php';
            new \MGModule\Multibrand\Loader();
            
            $hotingRel = Capsule::table('Multibrand_Relations')->where('type', 'hosting')->where('relid', $id)->first();
            
            $brandID = false;
            if(isset($hotingRel->brand_id) && !empty($hotingRel->brand_id))
            {
                $brandID = $hotingRel->brand_id;
            }
            
            if($brandID === false)
            {
                $brand = Capsule::table('Multibrand_Brands')->first();
                $brandID = $brand->id;
            }
            
            $settings = array();
            
            $BrandDetails = Capsule::table('Multibrand_Settings')->where('brand_id', $brandID)->get();
            $Brand = Capsule::table('Multibrand_Brands')->where('id', $brandID)->first();
            
            foreach($BrandDetails as $setting)
            {
                $settings[$setting->setting] = $setting->value;
            }
            
            $settings['name'] = $Brand->name;
            $settings['domain'] = $Brand->domain;

            $systemURL = \MGModule\Multibrand\Core\Server::getSystemURL($settings['domain']);
            
            $brandData['systemURL'] = $systemURL;
            $brandData['email'] = $settings['email'];
            $brandData['companyName'] = $settings['companyName'];            
            $brandData['domain'] = $settings['domain'];
            $brandData['LogoURL'] = $systemURL.'modules/addons/Multibrand/storage/logo/'.$settings['logo'];
            $brandData['LogoImage'] = '<img src="'.$systemURL.'modules/addons/Multibrand/storage/logo/'.$settings['logo'].'"/>';
            $brandData['whmcsURL'] = $systemURL;
            $brandData['whmcsLink'] = '<a href="'.$systemURL.'">'.$systemURL.'</a>';
            $brandData['signature'] = $settings['signature'];
            
            return $brandData;
            
        } else {
            
            $brandData['systemURL'] = $systemURL;
            
            return $brandData;
	}
    }
}
