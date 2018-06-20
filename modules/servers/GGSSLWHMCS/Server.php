<?php

namespace MGModule\GGSSLWHMCS;
use MGModule\GGSSLWHMCS as main;

/**
 * Description of Addon
 *
 * @author Michal Czech <michael@modulesgarden.com>
 * @SuppressWarnings(PHPMD)
 */
class Server extends main\mgLibs\process\AbstractMainDriver {
    /**
     * Return Tempalates Path
     * 
     * @param boolean $relative
     * @return string
     */
    static function getModuleTemplatesDir($relative = false){
        
        $dir = ($relative)?'':(__DIR__.DS);
        
        $dir .= 'templates'.DS;
        
        if(self::I()->isAdmin())
        {
            return $dir.'admin';
        }
        else
        {
            $template = $GLOBALS['CONFIG']['Template'];

            if(file_exists(__DIR__.DS.'templates'.DS.'clientarea'.DS.$template))
            {
                return $dir.'clientarea'.DS.$template;
            }
            else
            {
                return $dir.'clientarea'.DS.'default';
            }
        }
    }
    
    public function getAssetsURL() {
        if($this->isAdmin())
        {
            return '../modules/servers/'.$this->configuration()->systemName.'/templates/admin/assets';
        }
        else
        {
            return 'modules/servers/'.$this->configuration()->systemName.'/'.self::getModuleTemplatesDir(true).'/assets';
        }
    }
    
    public function getType() {
        return 'server';
    }

    public static function getMainDIR(){
        return __DIR__;
    }
    
    public static function getUrl() {
        return false;
    }

    static function genJSONUrl($page){
        if($this->isAdmin())
        {
            return false;
        }
        else
        {
            return 'modules/servers/'.$this->configuration()->systemName.'/'.self::getModuleTemplatesDir(true).'/assets';
        }
    }
    
    /**
     * Get HTML Client Ara Page
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param array $params
     * @param array $input
     * @return string
     */
    static function getHTMLClientAreaPage($params,$input){
        try{
            
            self::I()->isAdmin(false);
            self::I()->setMainLangContext();
            try{
                $page   = empty($input['mg-page'])?'home':$input['mg-page']; 
                $action = empty($input['mg-action'])?'index':$input['mg-action'];

                $input['params'] = $params;

                $output = array(
                    'templatefile' => self::I()->getModuleTemplatesDir(true).'/main'
                );

                $vars['assetsURL'] = self::I()->getAssetsURL();
                $vars['mainJSONURL'] = 'clientarea.php?action=productdetails&id='.$params['serviceid'].'&json=true';
                
                list($content,$success,$error) = self::I()->runControler($page,$action,$input,'HTML');
                
                if(self::I()->isDebug())
                {
                    $html = '<div style="color: #a94442;background-color: #f2dede;border-color: #dca7a7;font-size:20px;padding:10px;"><strong>Module is under development Mode!!!!!!!!!!!!!!!</strong></div>';
                    
                    if($langs = mgLibs\Lang::getMissingLangs())
                    {
                        $html .= '<pre>';
                        foreach($langs as $lk => $lang)
                        {
                            $html .= $lk." = '".$lang."';\n";
                        }
                        $html .= '</pre>';
                    }
                    
                    $content = $html.$content;
                }
                $vars['content'] = $content;
                $vars['success'] = $success;
                $vars['error'] = $error;
                
            } catch (\Exception $ex) {                
                self::dump($ex);
                main\mgLibs\error\Register::register($ex);
                $vars['error'] = main\mgLibs\Lang::absoluteT('generalError');
                if(method_exists($ex, 'getToken'))
                {
                    $vars['error'] .= ' '.main\mgLibs\Lang::absoluteT('token').$ex->getToken();
                }
            }

            $output['vars'] = $vars;
            return $output;
        } catch (\Exception $ex) {
            self::dump($ex);
            return $ex->getMessage();
        }
    }
    
    /**
     * Get Json Client Ara Page
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param array $params
     * @param array $input
     * @return string
     */
    static function getJSONClientAreaPage($params,array $input = array())
    {
        try{
            self::I()->isAdmin(false);
            self::I()->setMainLangContext();
            $content = array();

            $page   = empty($input['mg-page'])?'home':$input['mg-action']; 
            $action = empty($input['mg-action'])?'index':$input['mg-action'];
            
            $input['params'] = $params;
            
            try{
                
                list($result,$success,$error) = self::I()->runControler($page,$action,$input,'JSON'); 

                if($error)
                {
                    $content['error']  = $error;
                    $content['result'] = 'error';
                }
                elseif($success)
                {
                    $content['success'] = $success;
                    $content['result']  = 'success';
                }

                if($langs = mgLibs\Lang::getMissingLangs())
                {
                    $html = '<pre>';
                    foreach($langs as $lk => $lang)
                    {
                        $html .= $lk." = '".$lang."';\n";
                    }
                    $html .= '</pre>';

                    $content['error']  = $html;
                    $content['result'] = 'error';
                }

                $content['data']    = $result;
            } catch (\Exception $ex) {                
                self::dump($ex);
                $content['result'] = 'error';
                main\mgLibs\error\Register::register($ex);
                if($ex->getMessage()) {
                    $content['error'] = $ex->getMessage();
                } else {
                    $content['error'] = main\mgLibs\Lang::absoluteT('generalError');
                }
                if(method_exists($ex, 'getToken'))
                {
                    $content['error'] .= ' '.main\mgLibs\Lang::absoluteT('token').$ex->getToken();
                }
            }

            return '<JSONRESPONSE#'.json_encode($content).'#ENDJSONRESPONSE>';
        } catch (\Exception $ex) {
            self::dump($ex);
            return $ex->getMessage();
        }
    }
    
    /**
     * Run WHMCS Action
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $action
     * @param array $input
     * @return string
     */
    static function accountAction($action,$input)
    {
        try{
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();
            
            try{
                self::I()->runControler('actions',$action,array(
                    'params'    => $input
                ),'Action');
                return 'success';
            } 
            catch (\Exception $ex)
            {
                if(self::I()->isDebug())
                {
                    self::dump($ex);
                    die();
                }

                main\mgLibs\error\Register::register($ex);
                $message = $ex->getMessage();
                if(method_exists($ex, 'getToken'))
                {
                    $message .= ' '.main\mgLibs\Lang::absoluteT('token').$ex->getToken();
                }
                return $message;
            }
        } catch (\Exception $ex) {
            self::dump($ex);
            return $ex->getMessage();
        }
    }
    
    /**
     * Get HTML Product Config Page
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param array $input
     * @return string
     */  
    static function getHTMLProductConfigPage($input){
        try{     
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();
            
            $product = new models\whmcs\product\Product($input['id']);
            if($product->serverType !== self::I()->configuration()->systemName)
            {
                return false;
            }
            
            $page   = empty($input['mg-page'])?'configuration':$input['mg-page']; 
            $action = empty($input['mg-action'])?'index':$input['mg-action'];
            
            $input['params'] = array(
                'pid'   =>  $input['id']
            );
            
            $vars = array(
                'mainURL'       => 'configproducts.php?action=edit&id='.$input['id'].'&tab=2'
                ,'mainName'     => self::I()->configuration()->name
                ,'mainJSONURL'  => 'configproducts.php?action=edit&id='.$input['id'].'&json=1'
                ,'assetsURL'    => self::I()->getAssetsURL()
            );
            
            foreach(self::I()->configuration()->getServerMenu() as $pageName => $pageConfig){
                $pageConf = array(
                    'title' => main\mgLibs\Lang::absoluteT('pages',$pageName,'title')
                    ,'url'  => 'configproducts.php?action=edit&id='.$input['id'].'&tab=2&mg-page='.$pageName
                );
                
                if(!empty($pageConfig['icon']))
                {
                    $pageConf['icon'] = $pageConfig['icon'];
                }

                $vars['menu'][$pageName] = $pageConf;
            }

            try{       
                if(self::I()->isDebug())
                {
                    self::I()->configuration()->activate();
                }
                
                
                list($content,$success,$error) = self::I()->runControler($page,$action,$input,'HTML');
                
                $vars['content'] = $content;
                $vars['success'] = $success;
                $vars['error'] = $error;
            } catch (\Exception $ex) {
                self::dump($ex);
                main\mgLibs\error\Register::register($ex);
                $vars['error'] = $ex->getMessage();
                if(method_exists($ex, 'getToken'))
                {
                    $vars['error'] .= '. '.main\mgLibs\Lang::absoluteT('token').$ex->getToken();
                }
            }

            main\mgLibs\Smarty::I()->setTemplateDir(self::I()->getModuleTemplatesDir());
            $content = main\mgLibs\Smarty::I()->view('main',$vars);
            
            if(self::I()->isDebug())
            {
                $html = '<div style="color: #a94442;background-color: #f2dede;border-color: #dca7a7;font-size:20px;padding:10px;"><strong>Module is under development Mode!!!!!!!!!!!!!!!</strong></div>';

                if($langs = mgLibs\Lang::getMissingLangs())
                {
                    $html .= '<pre>';
                    foreach($langs as $lk => $lang)
                    {
                        $html .= $lk." = '".$lang."';\n";
                    }
                    $html .= '</pre>';
                }

                $content = $html.$content;
            }
            
            return $content;
            
        } catch (\Exception $ex) {
            self::dump($ex);
            return $ex->getMessage();
        }
    }
    
    /**
     * Get JSON Product Config Page
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param array $input
     * @return string
     */  
    static function getJSONProductConfigPage($input){
        try{
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();
            
            $content = array();

            $page   = empty($input['mg-page'])?'configuration':$input['mg-page']; 
            $action = empty($input['mg-action'])?'index':$input['mg-action'];

            $input['params'] = array(
                'pid'   =>  $input['id']
            );
            
            try{
                list($result,$success,$error) = self::I()->runControler($page,$action,$input,'JSON');
                
                if($error)
                {
                    $content['error']  = $error;
                    $content['result'] = 'error';
                }
                elseif($success)
                {
                    $content['success'] = $success;
                    $content['result']  = 'success';
                }

                if($langs = mgLibs\Lang::getMissingLangs())
                {
                    $html = '<pre>';
                    foreach($langs as $lk => $lang)
                    {
                        $html .= $lk." = '".$lang."';\n";
                    }
                    $html .= '</pre>';

                    $content['error']  = $html;
                    $content['result'] = 'error';
                }
            
                $content['data']    = $result;
            } catch (\Exception $ex) {
                self::dump($ex);
                main\mgLibs\error\Register::register($ex);
                $content['result'] = 'error';
                $content['error']  = $ex->getMessage();
                if(method_exists($ex, 'getToken'))
                {
                    $content['error'] .= ' '.main\mgLibs\Lang::absoluteT('token').$ex->getToken();
                }
            }

            return '<JSONRESPONSE#'.json_encode($content).'#ENDJSONRESPONSE>';
        } catch (\Exception $ex) {
            self::dump($ex);
            return $ex->getMessage();
        }
    }
    
    /**
     * Get Config Product Array
     * Useless if you inject own product configuration
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return array
     */  
    static function configArray(){        
        if(method_exists(self::I()->configuration(), 'getServerWHMCSConfig'))
        {
            $config = self::I()->configuration()->getServerWHMCSConfig();
            if(is_array($config))
            {
                return array_fill_keys($config, array("Type" => "text"));
            }
        }
        return array();
    }
}
