<?php

namespace Phi\Component;


use Phi\Component\Traits\HTMLComponent;
use Phi\Component\Traits\MustacheTemplate;
use Phi\Component\Traits\Collection;

class Component extends Template
{


    use HTMLComponent;

    protected $attributeXPathQuery = '/property[@name]';
    protected $attributeAttributeName = 'name';

    protected $sourceNode;


    protected $instanceID;


    protected static $instanceIndex = array();


    public function __construct($template = null)
    {
        parent::__construct($template);
        $this->generateID();

    }


    public function getElementID()
    {
        return $this->getVariable('elementID');
    }

    public function getID()
    {
        return $this->instanceID;
    }


    protected function generateID()
    {


        if ($this->instanceID !== null) {
            return $this->instanceID;
        }


        $className = basename(get_class($this));


        if (!isset(static::$instanceIndex[$className])) {
            static::$instanceIndex[$className] = -1;
        }
        static::$instanceIndex[$className]++;


        /*
        echo '<pre id="' . __FILE__ . '-' . __LINE__ . '" style="border: solid 1px rgb(255,0,0); background-color:rgb(255,255,255)">';
        echo '<div style="background-color:rgba(100,100,100,1); color: rgba(255,255,255,1)">' . __FILE__ . '@' . __LINE__ . '</div>';
        print_r($className);
        echo ' : '.static::$instanceIndex[$className];
        echo '</pre>';
        */


        $this->instanceID = str_replace('\\', '-', $className) . '-' . static::$instanceIndex[$className];

        //$this->instanceID=$className.'-'.md5(uniqid('', true));

        $this->setVariable('elementID', $this->instanceID);
    }


    public function loadFromDOMNode(\DOMElement $node)
    {
        $this->sourceNode = $node;
        $this->dom = $this->createDomDocumentFromNode($node);
        $this->extractParametersFromDOM($this->dom);
        return $this;
    }

    public function extractParametersFromDOM($dom)
    {


        if ($dom->firstChild->attributes->length) {

            foreach ($dom->firstChild->attributes as $attribute) {
                $this->setVariable($attribute->name, $attribute->value);
            }
        }


        $query = $dom->firstChild->getNodePath() . $this->attributeXPathQuery;


        $xPath = new \DOMXPath($dom);
        $nodes = $xPath->query($query);


        foreach ($nodes as $attributeNode) {
            /**
             * @var \DOMElement $attributeNode
             */

            $attributeName = (string)$attributeNode->getAttribute($this->attributeAttributeName);
            $value = $dom->innerHTML($attributeNode);


            if ($attributeNode->getAttribute('type') == 'json') {
                $value = json_decode($value, true);
            }

            $this->setVariable($attributeName, $value);

        }
    }


    public function bindAttributesValues($attributesValues)
    {


        foreach ($this->getVariables() as $variableName => $value) {

            $buffer = $value;
            preg_replace_callback('`\{\{\{(.*?)\}\}\}`', function ($matches) use ($variableName, $attributesValues) {

                $variables = explode('.', $matches[1]);

                $currentValue = null;

                if (isset($attributesValues[$variables[0]])) {

                    $currentValue = $attributesValues[$variables[0]];

                    array_shift($variables);

                    foreach ($variables as $subVariable) {
                        if (is_array($currentValue) && isset($currentValue[$subVariable])) {
                            $currentValue = $currentValue[$subVariable];
                        } else if (is_object($currentValue) && isset($currentValue->$subVariable)) {
                            $currentValue = $currentValue->$subVariable;
                        } else {
                            $currentValue = null;
                            break;
                        }
                    }
                }
                $this->setVariable($variableName, $currentValue);
            }, $buffer);
        }

        return $this;
    }


    public function __toString()
    {
        return $this->getOutput();
    }


}