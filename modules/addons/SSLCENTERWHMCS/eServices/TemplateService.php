<?php

namespace MGModule\SSLCENTERWHMCS\eServices;

class TemplateService {

    public static function buildTemplate($template, array $vars = []) {
        \MGModule\SSLCENTERWHMCS\Addon::I(true);
        $dir = \MGModule\SSLCENTERWHMCS\Addon::getModuleTemplatesDir();
        return \MGModule\SSLCENTERWHMCS\mgLibs\Smarty::I()->view($dir . '/' . $template, $vars);
        $path = $dir . '/' . $template;
        $path = str_replace('\\', '/', $path);
        return \MGModule\SSLCENTERWHMCS\mgLibs\Smarty::I()->view($path, $vars);
    }

   
}
