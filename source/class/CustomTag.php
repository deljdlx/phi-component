<?php

namespace PHPComponent;



class CustomTag
{


    protected $name;
    protected $renderer;

    protected $scripts=array();

    protected $scriptsByURL=array();





    protected $css=array();

    public function __construct($name, $renderer) {
        $this->name=$name;
        $this->renderer=$renderer;
    }



    public function getName() {
        return $this->name;
    }


    public function getScripts() {
        return $this->scripts;
    }



    public function addJavascript($script, $name=null) {
        if($name) {
            $this->scripts[$name]=$script;
        }
        else {
            $this->scripts[]=$script;
        }
        return $this;
    }

    public function addJavascriptByURL($url, $name=null) {
        if($name) {
            $this->scriptsByURL[$name]=$url;
        }
        else {
            $this->scriptsByURL[]=$url;
        }
        return $this;
    }





    public function render($innerHTLML, $node) {
        $content=call_user_func_array(array($this->renderer, '__invoke'), array($innerHTLML, $node));


        $scriptBuffer='';

        foreach ($this->scriptsByURL as $url) {
            $scriptBuffer.='<script src="'.$url.'"></script>';
        }


        foreach ($this->scripts as $script) {
            $scriptBuffer.='<script>'.$script.'</script>';
        }


        return $content.$scriptBuffer;
    }







}