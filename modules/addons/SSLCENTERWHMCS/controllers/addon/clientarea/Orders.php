<?php

namespace MGModule\SSLCENTERWHMCS\controllers\addon\clientarea;

use MGModule\SSLCENTERWHMCS as main;

/**
 * Description of home
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Orders extends main\mgLibs\process\AbstractController
{
    private $type= 'total';
    private $permittedTypes = array('total', 'unpaid', 'processing', 'expires_soon');

    public function indexHTML($input = array())
    {
        if (isset($_REQUEST['type']) && in_array($_REQUEST['type'], $this->permittedTypes))
            $this->type = $_REQUEST['type'];

        $vars['orderType'] = $this->type;
        $vars['assetsURL'] = main\Server::I()->getAssetsURL();
        $vars['pageTitle'] = main\mgLibs\Lang::getInstance()->absoluteT('addonCA', 'sslSummaryOrdersPage', 'pageTitle', $this->type);
        return array(
            'tpl'  => 'orders'
            , 'vars' => $vars
        );
    }

    public function listJSON()
    {
        if (isset($_REQUEST['type']))
            $this->type = $_REQUEST['type'];

        $orders          = array();
        $sslSummaryStats = new main\eHelpers\SSLSummary($_SESSION['uid']);

        switch ($this->type)
        {
            case 'unpaid';
                $orders = $sslSummaryStats->getUnpaidSSLOrders();
                break;
            case 'processing';
                $orders = $sslSummaryStats->getProcessingSSLOrders();
                break;
            case 'expires_soon';
                $orders = $sslSummaryStats->getExpiresSoonSSLOrders();
                break;
            case 'total':
            default:
                $orders = $sslSummaryStats->getTotalSSLOrders();
                break;
        }

        $preparedRows = array();
        
        foreach ($orders as $order)
        {
            $preparedRows[] = $this->prepareRow($order);
        }

        return array(
            'data' => $preparedRows
        );
    }

    private function prepareRow($order)
    {
        // Product/Service column
        $row[0] = '<form class="hidden" name="redirectToService" action="clientarea.php?action=productdetails&id=' . $order->getID() . '"  method="POST"></form><strong>' . $order->product()->getName() . '</strong>';
        if ($order->getDomain() != NULL)
            $row[0] .= '<br /><a href="' . $order->getDomain() . ' target="_blank">' . $order->getDomain() . '</a>';
        // Pricing column
        $amount = ($order->billingcycle() == "One Time" ? $order->getFirstPaymentAmount() : $order->getAmount());

        $row[1] = formatCurrency($amount) . '<br /> ' . $order->billingcycle();
        // Next Due Date column
        $row[2] = (!$order->getNextDueDate()) ? '-' : fromMySQLDate($order->getNextDueDate(), false, true);
        // Status column
        $row[3] = '<span class="label status status-' . strtolower($order->status()) . '">' . $order->status() . '</span>';


        return $row;
    }
}
