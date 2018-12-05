<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

class Whmcs
{

    public static function isWHMCS73()
    {
        $version = explode("-", $GLOBALS['CONFIG']['Version'], 2)[0];

        return version_compare($version, '7.3.0', '>=');
    }

    public static function getPricingInfo($pid, $commission = 0, $inclconfigops = false, $upgrade = false )
    {
        if(!$commission)
            $commission = 0;

        global $CONFIG;
        global $_LANG;
        global $currency;
        $result                 = select_query("tblproducts", "", array("id" => $pid));
        $data                   = mysql_fetch_array($result);
        $paytype                = $data["paytype"];
        $freedomain             = $data["freedomain"];
        $freedomainpaymentterms = $data["freedomainpaymentterms"];
        if (!isset($currency["id"]))
        {
            $currency = getCurrency();
        }

        $result                  = select_query("tblpricing", "", array("type" => "product", "currency" => $currency["id"], "relid" => $pid));
        $data                    = mysql_fetch_array($result);
        $msetupfee               = $data["msetupfee"];
        $qsetupfee               = $data["qsetupfee"];
        $ssetupfee               = $data["ssetupfee"];
        $asetupfee               = $data["asetupfee"];
        $bsetupfee               = $data["bsetupfee"];
        $tsetupfee               = $data["tsetupfee"];
        $monthly                 = (string)((float)$data["monthly"] + (float)$data["monthly"] * (float)$commission);
        $quarterly               = (string)((float)$data["quarterly"] + (float)$data["quarterly"] * (float)$commission);
        $semiannually            = (string)((float)$data["semiannually"] + (float)$data["semiannually"] * (float)$commission);
        $annually                = (string)((float)$data["annually"] + (float)$data["annually"] * (float)$commission);
        $biennially              = (string)((float)$data["biennially"] + (float)$data["biennially"] * (float)$commission);
        $triennially             = (string)((float)$data["triennially"] + (float)$data["triennially"] * (float)$commission);
        $configoptions           = new \WHMCS\Product\ConfigOptions();
        $freedomainpaymentterms  = explode(",", $freedomainpaymentterms);
        $monthlypricingbreakdown = $CONFIG["ProductMonthlyPricingBreakdown"];
        $minprice                = 0;
        $setupFee                = 0;
        $mincycle                = "";
        $hasconfigoptions        = false;
        if ($paytype == "free")
        {
            $pricing["type"] = $mincycle        = "free";
        }
        else
        {
            if ($paytype == "onetime")
            {
                if ($inclconfigops)
                {
                    $msetupfee += $configoptions->getBasePrice($pid, "msetupfee");
                    $monthly   += $configoptions->getBasePrice($pid, "monthly");
                }

                $minprice           = $monthly;
                $setupFee           = $msetupfee;
                $pricing["type"]    = $mincycle           = "onetime";
                $pricing["onetime"] = new \WHMCS\View\Formatter\Price($monthly, $currency);
                if ($msetupfee != "0.00")
                {
                    $pricing["onetime"] .= " + " . new \WHMCS\View\Formatter\Price($msetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                }

                if (in_array("onetime", $freedomainpaymentterms) && $freedomain && !$upgrade)
                {
                    $pricing["onetime"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                }
            }
            else
            {
                if ($paytype == "recurring")
                {
                    $pricing["type"] = "recurring";
                    if (0 <= $monthly)
                    {
                        if ($inclconfigops)
                        {
                            $msetupfee += $configoptions->getBasePrice($pid, "msetupfee");
                            $monthly   += $configoptions->getBasePrice($pid, "monthly");
                        }

                        if (!$mincycle)
                        {
                            $minprice  = $monthly;
                            $setupFee  = $msetupfee;
                            $mincycle  = "monthly";
                            $minMonths = 1;
                        }

                        if ($monthlypricingbreakdown)
                        {
                            $pricing["monthly"] = $_LANG["orderpaymentterm1month"] . " - " . new \WHMCS\View\Formatter\Price($monthly, $currency);
                        }
                        else
                        {
                            $pricing["monthly"] = new \WHMCS\View\Formatter\Price($monthly, $currency) . " " . $_LANG["orderpaymenttermmonthly"];
                        }

                        if ($msetupfee != "0.00")
                        {
                            $pricing["monthly"] .= " + " . new \WHMCS\View\Formatter\Price($msetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                        }

                        if (in_array("monthly", $freedomainpaymentterms) && $freedomain && !$upgrade)
                        {
                            $pricing["monthly"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                        }
                    }

                    if (0 <= $quarterly)
                    {
                        if ($inclconfigops)
                        {
                            $qsetupfee += $configoptions->getBasePrice($pid, "qsetupfee");
                            $quarterly += $configoptions->getBasePrice($pid, "quarterly");
                        }

                        if (!$mincycle)
                        {
                            $minprice  = ($monthlypricingbreakdown ? $quarterly / 3 : $quarterly);
                            $setupFee  = $qsetupfee;
                            $mincycle  = "quarterly";
                            $minMonths = 3;
                        }

                        if ($monthlypricingbreakdown)
                        {
                            $pricing["quarterly"] = $_LANG["orderpaymentterm3month"] . " - " . new \WHMCS\View\Formatter\Price($quarterly / 3, $currency);
                        }
                        else
                        {
                            $pricing["quarterly"] = new \WHMCS\View\Formatter\Price($quarterly, $currency) . " " . $_LANG["orderpaymenttermquarterly"];
                        }

                        if ($qsetupfee != "0.00")
                        {
                            $pricing["quarterly"] .= " + " . new \WHMCS\View\Formatter\Price($qsetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                        }

                        if (in_array("quarterly", $freedomainpaymentterms) && $freedomain && !$upgrade)
                        {
                            $pricing["quarterly"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                        }
                    }

                    if (0 <= $semiannually)
                    {
                        if ($inclconfigops)
                        {
                            $ssetupfee    += $configoptions->getBasePrice($pid, "ssetupfee");
                            $semiannually += $configoptions->getBasePrice($pid, "semiannually");
                        }

                        if (!$mincycle)
                        {
                            $minprice  = ($monthlypricingbreakdown ? $semiannually / 6 : $semiannually);
                            $setupFee  = $ssetupfee;
                            $mincycle  = "semiannually";
                            $minMonths = 6;
                        }

                        if ($monthlypricingbreakdown)
                        {
                            $pricing["semiannually"] = $_LANG["orderpaymentterm6month"] . " - " . new \WHMCS\View\Formatter\Price($semiannually / 6, $currency);
                        }
                        else
                        {
                            $pricing["semiannually"] = new \WHMCS\View\Formatter\Price($semiannually, $currency) . " " . $_LANG["orderpaymenttermsemiannually"];
                        }

                        if ($ssetupfee != "0.00")
                        {
                            $pricing["semiannually"] .= " + " . new \WHMCS\View\Formatter\Price($ssetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                        }

                        if (in_array("semiannually", $freedomainpaymentterms) && $freedomain && !$upgrade)
                        {
                            $pricing["semiannually"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                        }
                    }

                    if (0 <= $annually)
                    {
                        if ($inclconfigops)
                        {
                            $asetupfee += $configoptions->getBasePrice($pid, "asetupfee");
                            $annually  += $configoptions->getBasePrice($pid, "annually");
                        }

                        if (!$mincycle)
                        {
                            $minprice  = ($monthlypricingbreakdown ? $annually / 12 : $annually);
                            $setupFee  = $asetupfee;
                            $mincycle  = "annually";
                            $minMonths = 12;
                        }

                        if ($monthlypricingbreakdown)
                        {
                            $pricing["annually"] = $_LANG["orderpaymentterm12month"] . " - " . new \WHMCS\View\Formatter\Price($annually / 12, $currency);
                        }
                        else
                        {
                            $pricing["annually"] = new \WHMCS\View\Formatter\Price($annually, $currency) . " " . $_LANG["orderpaymenttermannually"];
                        }

                        if ($asetupfee != "0.00")
                        {
                            $pricing["annually"] .= " + " . new \WHMCS\View\Formatter\Price($asetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                        }

                        if (in_array("annually", $freedomainpaymentterms) && $freedomain && !$upgrade)
                        {
                            $pricing["annually"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                        }
                    }

                    if (0 <= $biennially)
                    {
                        if ($inclconfigops)
                        {
                            $bsetupfee  += $configoptions->getBasePrice($pid, "bsetupfee");
                            $biennially += $configoptions->getBasePrice($pid, "biennially");
                        }

                        if (!$mincycle)
                        {
                            $minprice  = ($monthlypricingbreakdown ? $biennially / 24 : $biennially);
                            $setupFee  = $bsetupfee;
                            $mincycle  = "biennially";
                            $minMonths = 24;
                        }

                        if ($monthlypricingbreakdown)
                        {
                            $pricing["biennially"] = $_LANG["orderpaymentterm24month"] . " - " . new \WHMCS\View\Formatter\Price($biennially / 24, $currency);
                        }
                        else
                        {
                            $pricing["biennially"] = new \WHMCS\View\Formatter\Price($biennially, $currency) . " " . $_LANG["orderpaymenttermbiennially"];
                        }

                        if ($bsetupfee != "0.00")
                        {
                            $pricing["biennially"] .= " + " . new \WHMCS\View\Formatter\Price($bsetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                        }

                        if (in_array("biennially", $freedomainpaymentterms) && $freedomain && !$upgrade)
                        {
                            $pricing["biennially"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                        }
                    }

                    if (0 <= $triennially)
                    {
                        if ($inclconfigops)
                        {
                            $tsetupfee   += $configoptions->getBasePrice($pid, "tsetupfee");
                            $triennially += $configoptions->getBasePrice($pid, "triennially");
                        }

                        if (!$mincycle)
                        {
                            $minprice  = ($monthlypricingbreakdown ? $triennially / 36 : $triennially);
                            $setupFee  = $tsetupfee;
                            $mincycle  = "triennially";
                            $minMonths = 36;
                        }

                        if ($monthlypricingbreakdown)
                        {
                            $pricing["triennially"] = $_LANG["orderpaymentterm36month"] . " - " . new \WHMCS\View\Formatter\Price($triennially / 36, $currency);
                        }
                        else
                        {
                            $pricing["triennially"] = new \WHMCS\View\Formatter\Price($triennially, $currency) . " " . $_LANG["orderpaymenttermtriennially"];
                        }

                        if ($tsetupfee != "0.00")
                        {
                            $pricing["triennially"] .= " + " . new \WHMCS\View\Formatter\Price($tsetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                        }

                        if (in_array("triennially", $freedomainpaymentterms) && $freedomain && !$upgrade)
                        {
                            $pricing["triennially"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                        }
                    }
                }
            }
        }

        $pricing["hasconfigoptions"] = $configoptions->hasConfigOptions($pid);
        if (isset($pricing["onetime"]))
        {
            $pricing["cycles"]["onetime"] = $pricing["onetime"];
        }

        if (isset($pricing["monthly"]))
        {
            $pricing["cycles"]["monthly"] = $pricing["monthly"];
        }

        if (isset($pricing["quarterly"]))
        {
            $pricing["cycles"]["quarterly"] = $pricing["quarterly"];
        }

        if (isset($pricing["semiannually"]))
        {
            $pricing["cycles"]["semiannually"] = $pricing["semiannually"];
        }

        if (isset($pricing["annually"]))
        {
            $pricing["cycles"]["annually"] = $pricing["annually"];
        }

        if (isset($pricing["biennially"]))
        {
            $pricing["cycles"]["biennially"] = $pricing["biennially"];
        }

        if (isset($pricing["triennially"]))
        {
            $pricing["cycles"]["triennially"] = $pricing["triennially"];
        }

        $pricing["rawpricing"] = array("msetupfee" => format_as_currency($msetupfee), "qsetupfee" => format_as_currency($qsetupfee), "ssetupfee" => format_as_currency($ssetupfee), "asetupfee" => format_as_currency($asetupfee), "bsetupfee" => format_as_currency($bsetupfee), "tsetupfee" => format_as_currency($tsetupfee), "monthly" => format_as_currency($monthly), "quarterly" => format_as_currency($quarterly), "semiannually" => format_as_currency($semiannually), "annually" => format_as_currency($annually), "biennially" => format_as_currency($biennially), "triennially" => format_as_currency($triennially));
        $pricing["minprice"]   = array("price" => new \WHMCS\View\Formatter\Price($minprice, $currency), "setupFee" => (0 < $setupFee ? new \WHMCS\View\Formatter\Price($setupFee, $currency) : 0), "cycle" => ($monthlypricingbreakdown && $paytype == "recurring" ? "monthly" : $mincycle), "simple" => (new \WHMCS\View\Formatter\Price($minprice, $currency))->toPrefixed());
        if (isset($minMonths))
        {
            switch ($minMonths)
            {
                case 3:
                    $langVar = "shoppingCartProductPerMonth";
                    $count   = "3 ";
                    break;
                case 6:
                    $langVar = "shoppingCartProductPerMonth";
                    $count   = "6 ";
                    break;
                case 12:
                    $langVar = ($monthlypricingbreakdown ? "shoppingCartProductPerMonth" : "shoppingCartProductPerYear");
                    $count   = "";
                    break;
                case 24:
                    $langVar = ($monthlypricingbreakdown ? "shoppingCartProductPerMonth" : "shoppingCartProductPerYear");
                    $count   = "2 ";
                    break;
                case 36:
                    $langVar = ($monthlypricingbreakdown ? "shoppingCartProductPerMonth" : "shoppingCartProductPerYear");
                    $count   = "3 ";
                    break;
                default:
                    $langVar = "shoppingCartProductPerMonth";
                    $count   = "";
            }
            $pricing["minprice"]["cycleText"]             = \Lang::trans($langVar, array(":count" => $count, ":price" => $pricing["minprice"]["simple"]));
            $pricing["minprice"]["cycleTextWithCurrency"] = \Lang::trans($langVar, array(":count" => $count, ":price" => $pricing["minprice"]["price"]));
        }

        return $pricing;
    }
}
