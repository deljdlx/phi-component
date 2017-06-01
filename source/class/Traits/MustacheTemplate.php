<?php

namespace Phi\Component\Traits;


Trait MustacheTemplate
{





    public function compileMustache($buffer, $variables) {
        $mustacheEngine=new \Mustache_Engine();
        $compiled=$mustacheEngine->render($buffer, $variables);

        return $compiled;
    }

}
