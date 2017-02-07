<?php

namespace PHPComponent;


use PHPComponent\Traits\HTMLComponent;
use PHPComponent\Traits\MustacheTemplate;
use PHPComponent\Traits\Collection;

class Component extends Template
{


    protected $attributeXPathQuery = '/property[@name]';
    protected $attributeAttributeName = 'name';

    use HTMLComponent;


    protected $sourceNode;


    public function __construct($template = null)
    {
        parent::__construct($template);
        $this->generateID();
        $this->setVariable('elementID', $this->getID());
    }

    public function getElementID() {
        return $this->getVariable('elementID');
    }

    public function getID()
    {
        return $this->instanceID;
    }


    protected function generateID()
    {
        $className = basename(get_class($this));

        if (!isset(static::$instanceIndex[$className])) {
            static::$instanceIndex[$className] = -1;
        }
        static::$instanceIndex[$className]++;
        $this->instanceID = str_replace('\\', '-', $className) . '-' . static::$instanceIndex[$className];

        $this->setVariable('elementID', $this->instanceID);
    }




    public function loadFromDOMNode(\DOMElement $node)
    {
        $this->sourceNode=$node;
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

    /**
     * @param null $template
     * @param null $values
     * @return string
     */
    public function render($template = null, $values = null, $renderer=null)
    {

        $this->initializeRendering($template, $values, $renderer);
        $output = $this->compileMustache($this->template, $this->getVariables());


        $this->output = $this->doAfterRendering($output);

        return $this->output;
    }


    public function __toString()
    {
        return $this->getOutput();
    }


}