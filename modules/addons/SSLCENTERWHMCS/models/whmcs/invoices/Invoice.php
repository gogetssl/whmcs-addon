<?php

/* * ********************************************************************
 * SSLCENTERWHMCS product developed. (2015-12-09)
 * *
 *
 *  CREATED BY MODULESGARDEN       ->       http://modulesgarden.com
 *  CONTACT                        ->       contact@modulesgarden.com
 *
 *
 * This software is furnished under a license and may be used and copied
 * only  in  accordance  with  the  terms  of such  license and with the
 * inclusion of the above copyright notice.  This software  or any other
 * copies thereof may not be provided or otherwise made available to any
 * other person.  No title to and  ownership of the  software is  hereby
 * transferred.
 *
 *
 * ******************************************************************** */

namespace MGModule\SSLCENTERWHMCS\models\whmcs\invoices;
use MGModule\SSLCENTERWHMCS as main;

/**
 * Description of Item
 *
 * @author Pawel Kopec <pawelk@modulesgarden.com>
 * @Table(name=tblinvoices,preventUpdate,prefixed=false)
 * 
 */
class Invoice extends main\mgLibs\models\Orm{
    
    /**
     * @Column()
     * @var int
     */
    protected $id;
    
     /**
     * @Column(name=userid,as=userId)
     * @var int
     */
    protected $userId;
    
     /**
     * @Column(name=invoicenum,as=invoiceNum)
     * @var int
     */
    protected $invoiceNum;
    
     /**
     * @Column(name=date)
     * @var string
     */
    protected $date;
    
     /**
     * @Column(name=duedate)
     * @var string
     */
    protected $duedate;
    
     /**
     * @Column(name=datepaid)
     * @var string
     */
    protected $datepaid;
    
    /**
     * @Column(name=status)
     * @var string
     */
    protected $status;
    
     /**
     * @Column(name=subtotal)
     * @var string
     */
    protected $subtotal;
    
    private $_client;
    
    protected $_items;
    
    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getInvoiceNum() {
        return $this->invoiceNum;
    }

    public function getDate() {
        return $this->date;
    }

    public function getDuedate() {
        return $this->duedate;
    }

    public function getDatepaid() {
        return $this->datepaid;
    }

    public function getSubtotal() {
        return $this->subtotal;
    }
    
    public function getStatus() {
        return $this->status;
    }

    /**
     * 
     * @return main\models\whmcs\clients\Client
     */
    public function getClient() {
        if(!empty($this->_client))
            return $this->_client;
        return $this->_client = new main\models\whmcs\clients\Client($this->getUserId());
    }
    
    /**
     * 
     * @return main\models\whmcs\invoices\item
     */
    public function items() {
        if(!empty($this->_items))
            return $this->_items;
        
        $itemsRepository = new main\models\whmcs\invoices\RepositoryItem();
        $itemsRepository->onlyInvoiceId($this->id);
        $this->_items = $itemsRepository->get();
        
        return $this->_items;
    }

    /**
     * 
     * @param  int $id
     * @return \MGModule\SSLCENTERWHMCS\models\whmcs\invoices\Invoice
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * 
     * @param int $userId
     * @return \MGModule\SSLCENTERWHMCS\models\whmcs\invoices\Invoice
     */
    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }

    /**
     * 
     * @param int $invoiceNum
     * @return \MGModule\SSLCENTERWHMCS\models\whmcs\invoices\Invoice
     */
    public function setInvoiceNum($invoiceNum) {
        $this->invoiceNum = $invoiceNum;
        return $this;
    }

    /**
     * 
     * @param string $date
     * @return \MGModule\SSLCENTERWHMCS\models\whmcs\invoices\Invoice
     */
    public function setDate($date) {
        $this->date = $date;
        return $this;
    }

    /**
     * 
     * @param string $duedate
     * @return \MGModule\SSLCENTERWHMCS\models\whmcs\invoices\Invoice
     */
    public function setDuedate($duedate) {
        $this->duedate = $duedate;
        return $this;
    }

    /**
     * 
     * @param string $datepaid
     * @return \MGModule\SSLCENTERWHMCS\models\whmcs\invoices\Invoice]
     */
    public function setDatepaid($datepaid) {
        $this->datepaid = $datepaid;
        return $this;
    }

    /**
     * 
     * @param string $subtotal
     * @return \MGModule\SSLCENTERWHMCS\models\whmcs\invoices\Invoice
     */
    public function setSubtotal($subtotal) {
        $this->subtotal = $subtotal;
        return $this;
    }



    
    
}
