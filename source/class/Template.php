<?php

namespace PHPComponent;


use PHPComponent\DOMDocument;
use PHPComponent\Traits\MustacheTemplate;
use PHPComponent\Traits\Collection;

class Template
{


    use Collection;
    use MustacheTemplate;


    protected $output;
    protected  $template;


    protected $libXMLFlag;
    protected $dom;
    protected $rootNode;


    protected $customTags=array();

    protected $componentEnabled=false;

    protected $defaultComponentTagName='phi-component';
    protected $componantClassNameAttributeName='data-instanceof';

	protected $components=array();




    public function __construct($template=null) {
        $this->libXMLFlag=
            \LIBXML_HTML_NOIMPLIED
            | \LIBXML_HTML_NODEFDTD
            | \LIBXML_NOXMLDECL
            //| \LIBXML_NOERROR
            //| \LIBXML_NOWARNING
            //| \LIBXML_ERR_NONE
        ;

        $this->template=$template;
    }

    public function setTemplate($template) {
        $this->template=$template;
        return $this;
    }


    public function registerCustomTag($tagName, $callback) {

        $customTag=new CustomTag($tagName, $callback);

        $this->customTags[$tagName]=$customTag;//$callback;


        return $customTag;
        return $this;
    }

    public function enableComponents($value=true) {
        $this->componentEnabled=$value;
        return $this;
    }



    public function createDomDocumentFromNode(\DOMElement $node) {

        $valueNode=$node->cloneNode(true);

        $valueDocument=new DOMDocument('1.0', 'utf-8');
        $importedValueNode=$valueDocument->importNode($valueNode, true);
        $valueDocument->appendChild($importedValueNode);

        return $valueDocument;

    }





    public function parseDOM($buffer) {

        //libxml_use_internal_errors(true);

        $this->dom=new DOMDocument('1.0', 'utf-8');
        $this->dom->loadXML($buffer, $this->libXMLFlag);
        //libxml_clear_errors();


        $this->rootNode=$this->dom->firstChild;

        if($this->componentEnabled) {
            $this->initializeComponentParsing();
        }


        foreach ($this->customTags as $tagName=>$customTag) {
            $query='//'.$tagName;


            $xPath=new \DOMXPath($this->dom);
            $nodes=$xPath->query($query);




            foreach ($nodes as $node) {

                $nodeContent=$this->dom->innerHTML($node);
                $content=call_user_func_array(array($customTag, 'render'), array($nodeContent, $node));
                $this->dom->replaceNodeWithContent($node, $content);
            }
        }

        return $this->dom->saveHTML();
    }


    public function initializeComponentParsing() {


    	$template=$this;

        $this->registerCustomTag($this->defaultComponentTagName, function($nodeContent, \DOMElement $node) use ($template) {

            $className=(string) $node->getAttribute($this->componantClassNameAttributeName);

            if(class_exists($className)) {


	            $component=new $className;
	            $component->loadFromDOMNode($node);
	            $component->bindAttributesValues($this->getVariables());

                return $component;
            }

            return '';
        });
    }



    public function render($template=null, $values=null) {

        if($template) {
            $this->template=$template;
        }


        if(count($values)) {
            $this->setVariables($values);
        }





        $compiledDom=$this->parseDOM($this->template);

	    $output=$this->compileMustache($compiledDom, $this->getVariables());


        $this->output=$output;
        return $this->output;
    }




    public function getOutput($template=null, $values=null) {
        if($this->output===null) {
            return $this->render($template, $values);
        }
        else {
            return $this->output;
        }
    }

}
