<?php

namespace MGModule\SSLCENTERWHMCS\eHelpers;

define('DS', DIRECTORY_SEPARATOR);
define('WHMCS_MAIN_DIR', substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), 'modules' . DS . 'addons')));

use Exception;
use MGModule\SSLCENTERWHMCS as main;
use Illuminate\Database\Capsule\Manager as Capsule;

class Migration
{
    private static $instance;
    private static $moduleLoaded = false;
    const MODULE_NAME                  = 'GGSSLWHMCS';

    public function __construct()
    {
        if ($this->isModuleExist(self::MODULE_NAME))
        {
            $moduleLoaderPath = WHMCS_MAIN_DIR . DS . 'modules' . DS . 'addons' . DS . self::MODULE_NAME . DS . 'Loader.php';
            if (file_exists($moduleLoaderPath))
            {
                require_once $moduleLoaderPath;
                new \MGModule\GGSSLWHMCS\Loader();

                self::$moduleLoaded = true;
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new Migration();
        }
        return self::$instance;
    }

    public function run()
    {
        try
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: Data & Configuration Migration Started');
            
            #import:
            #configuration
            $this->importModuleConfiguration();
            #invoices_info
            $this->importInvoicesInfo();
            #update products
            $this->updateExistingProducts();
            #update SSL orders
            $this->updateSSLOrders();
            
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: Data & Configuration Migration Completed');
        }
        catch (Exception $ex)
        {
            throw new Exception($ex->getMessage());
        }
    }

    private function importModuleConfiguration()
    {
        if (self::$moduleLoaded)
        {
            
            $apiConfigRepo = new \MGModule\GGSSLWHMCS\models\apiConfiguration\Repository();
            $input         = (array) $apiConfigRepo->get();

            $apiConfigRepo = new \MGModule\SSLCENTERWHMCS\models\apiConfiguration\Repository();
            $apiConfigRepo->setConfiguration($input);
            
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: Module '.self::MODULE_NAME.' configuration imported successfully.');
        }
        else
        {
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: Module '.self::MODULE_NAME.' installation not detected.');
        }
    }

    private function importInvoicesInfo()
    {
        if (self::$moduleLoaded)
        {
            $oldTableName = \MGModule\GGSSLWHMCS\eHelpers\Invoice::INVOICE_INFOS_TABLE_NAME;
            $newTableName = main\eHelpers\Invoice::INVOICE_INFOS_TABLE_NAME;

            $fields = array(
                'user_id',
                'invoice_id',
                'service_id',
                'product_id',
                'order_id',
                'new_service_id',
                'status',
                'created_at',
                'updated_at',
            );
            $query  = 'INSERT INTO  `' . $newTableName . '` SELECT `' . $oldTableName . '`.* FROM `' . $oldTableName . '` ON DUPLICATE KEY UPDATE ';
            foreach ($fields as $i => $field)
            {
                $query .= ($i > 0 ? ', ' : '') . $field . '=VALUES(' . $field . ')';
            }

            main\mgLibs\MySQL\Query::query($query);
        }
    }

    private function updateExistingProducts()
    {

        $productsRepo = new main\models\whmcs\product\Products();
        $productsRepo->onlyModule(self::MODULE_NAME);

        foreach ($productsRepo->get() as $product)
        {
            $product->setServerType(main\Addon::I()->configuration()->systemName);
            $product->save();
            
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: Product ID: ' . $product->id . ' has been modified. Module changed from ' . self::MODULE_NAME . ' to ' . main\Addon::I()->configuration()->systemName);
        }
    }

    private function updateSSLOrders()
    {
        $SSLOrders = new main\eRepository\whmcs\service\SSL();
        $orders    = $SSLOrders->getBy(array('module' => self::MODULE_NAME));

        foreach ($orders as $ssl)
        {
            Capsule::table('tblsslorders')
                    ->where('id', '=', $ssl->id)
                    ->where('remoteid', '=', $ssl->remoteid)
                    ->update([
                        'module' => main\Addon::I()->configuration()->systemName
                    ]);
            
            main\eHelpers\Whmcs::savelogActivitySSLCenter('SSLCENTER WHMCS: SSL Service ID: ' . $ssl->serviceid . ' has been modified. Module changed from ' . self::MODULE_NAME . ' to ' . main\Addon::I()->configuration()->systemName);

        }
    }

    private function isModuleExist()
    {
        $result = main\mgLibs\MySQL\Query::select(
                        array(
                    'value'
                        )
                        , 'tblconfiguration'
                        , array(
                    'setting' => 'ActiveAddonModules'
                        )
        );

        while ($row = $result->fetch())
        {
            $activeModules = $row['value'];
        }

        $activeModulesArray = explode(',', $activeModules);
        foreach ($activeModulesArray as $module)
        {
            if ($module == self::MODULE_NAME)
                return true;
        }

        return false;
    }
}
