<?php

namespace PHPComponent;


use PHPComponent\DOMDocument;
use PHPComponent\Traits\MustacheTemplate;
use PHPComponent\Traits\Collection;

class Page extends Template
{





    public function render($template = null, $values = null)
    {

        $output=parent::render($template, $values);



        return $this->injectCSS($output);

    }


    public function injectCSS($buffer) {

        $cssBuffer='';
        foreach ($this->getComponents() as $component) {
            $cssBuffer.=$component->getGlobalCSS();
        }



        $output=preg_replace('`</head>`i', '<style>'.$cssBuffer.'</style></head>', $buffer);
        return $output;
    }


}
