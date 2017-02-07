<?php

namespace PHPComponent\Traits;


Trait HTMLComponent
{


    protected static $instanceIndex = array();
    protected $instanceID = '';


    protected static $globalCSS = array();
    protected $css = array();

    protected static $globalCSSFiles = array();
    protected $cssFiles = array();


    protected static $globalJavascripts = array();
    protected $javascripts = array();


    //=======================================================
    public static function addGlobalJavascript($javascript, $isURL = false)
    {
        //$name = get_called_class() . '-' . $sufix;

        $name = md5($javascript) . sha1($javascript);

        static::$globalJavascripts[$name] = array(
            'declaration'=>$javascript,
            'isURL'=>$isURL
        );

        return static::$globalJavascripts;
    }

    public static function getGlobalJavascripts($toString=true)
    {
        if(!$toString) {
            return static::$globalJavascripts;
        }

        $buffer='';
        foreach (static::$globalJavascripts as $descriptor) {
            if($descriptor['isURL']) {
                $buffer.='<script src="'.$descriptor['declaration'].'"></script>';
            }
            else {
                $buffer.='<script>
                    '.$descriptor['declaration'].'
                </script>';
            }
        }


    }


    public function addJavascript($javascript, $sufix = null)
    {
        $name = $this->getID() . $sufix;

        $this->javascripts[$this->getID()] = $javascript;

        return $this->javascripts;
    }

    public function getJavascript()
    {
        return implode("\n", $this->javascripts);
    }


    //=======================================================


    public function addCSS($cssDeclaration, $name = null)
    {
        if ($name === null) {
            $name = get_called_class();
        }

        $this->css[$name] = $cssDeclaration;

        return $this;
    }

    public static function addGlobalCSS($cssDeclaration, $isURL = false)
    {
        $name = md5($cssDeclaration) . sha1($cssDeclaration);

        static::$globalCSS[$name] = array(
            'declaration' => $cssDeclaration,
            'isURL' => $isURL
        );

        return static::$globalCSS[$name];
    }


    public function getCSS($toString = true, $withGlobalCSS = false)
    {
        if ($toString) {
            $css = implode('', $this->css);
            if ($withGlobalCSS) {
                return implode('', static::$globalCSS) . $css;
            }
        } else {
            return $this->css;
        }
    }



    public static function getGlobalCSS($toString = true)
    {
        if (!$toString) {
            return static::$globalCSS;
        }

        $buffer = '';
        foreach (static::$globalCSS as $descriptor) {
            if ($descriptor['isURL']) {
                $buffer .= '<link rel="stylesheet" href="' . $descriptor['declaration'] . '"/>';
            } else {
                $buffer .= '<style>' . $descriptor['declaration'] . '</style>';
            }
        }
        return $buffer;
    }


}
