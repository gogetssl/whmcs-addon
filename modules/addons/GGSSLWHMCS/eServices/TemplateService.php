<?php

namespace MGModule\GGSSLWHMCS\eServices;

class TemplateService {

    public static function buildTemplate($template, array $vars = []) {
        \MGModule\GGSSLWHMCS\Addon::I(true);
        $dir = \MGModule\GGSSLWHMCS\Addon::getModuleTemplatesDir();
        return \MGModule\GGSSLWHMCS\mgLibs\Smarty::I()->view($dir . '/' . $template, $vars);
        $path = $dir . '/' . $template;
        $path = str_replace('\\', '/', $path);
        return \MGModule\GGSSLWHMCS\mgLibs\Smarty::I()->view($path, $vars);
    }

   
}
