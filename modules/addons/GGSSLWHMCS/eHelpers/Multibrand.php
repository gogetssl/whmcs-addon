<?php

namespace MGModule\GGSSLWHMCS\eHelpers;

class Multibrand {
    public static function getBrandData($id) {
        
        if(function_exists('MultibrandFunctionalityAutoLoader')) {
            
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
        } else {
            return false;
	}
    }
}
