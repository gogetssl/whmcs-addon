<?php

namespace MGModule\GGSSLWHMCS\eModels\gogetssl;

use Exception;

class Product {

    private $webServerMap = [
        'comodo'   => 1,
        'rapidssl' => 2,
        'thawte'   => 2,
        'symantec' => 2,
        'geotrust' => 2,
    ];

    public function getWebServerTypeId() {
        if (isset($this->webServerMap[$this->brand])) {
            return $this->webServerMap[$this->brand];
        }
        throw new Exception('Provided brand are not supported.');
    }
    
    public function isOrganizationRequired() {
        return $this->org_required === 1;
    }
    
    public function isSanEnabled() {
        return $this->san_enabled === 1;
    }
    
    public function getPeriods() {
        $peroids = [];
        foreach ($this->prices['vendor'] as $peroid => $price) {
            if($peroid <= $this->max_period){
                $peroids[] = $peroid;
            }
        }
        return $peroids;
    }
    
    public function getMinimalPeriods() {
        $periods = $this->getPeriods();
        return reset($periods);
    }
    
    public function getPayType() {
        if(strpos(strtolower($this->product), 'trial') === false) {
            return 'recurring';
        }
        return 'free';
    }

}
