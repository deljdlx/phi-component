<?php
namespace PHPComponent\Traits;

Trait Introspectable
{


    protected $introspectionReflector=null;


    protected function initializeReflector() {
        if(!$this->introspectionReflector) {
            $this->introspectionReflector=new \ReflectionClass($this);
        }
        return $this;
    }

    public function getClassBaseName() {
        return basename(str_replace('\\', '/', get_class($this)));
    }

    public function getDefinitionFile() {
        $this->initializeReflector();
        return $this->introspectionReflector->getFileName();
    }

    public function getDefinitionFolder() {
        return dirname($this->getDefinitionFile());
    }



}