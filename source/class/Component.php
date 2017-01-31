<?php

namespace PHPComponent;


use PHPComponent\Traits\MustacheTemplate;
use PHPComponent\Traits\Collection;

class Component extends Template
{


    protected $attributeXPathQuery = '//meta[@data-attribute-name]';
    protected $attributeAttributeName = 'data-attribute-name';


    public function __construct($template = null)
    {
        parent::__construct($template);
    }

    public function loadFromDOMNode(\DOMElement $node)
    {


        $this->dom = $this->createDomDocumentFromNode($node);
        $this->extractParametersFromDOM($this->dom);
        return $this;
    }

    public function extractParametersFromDOM($dom)
    {

        $query = $this->attributeXPathQuery;

        $xPath = new \DOMXPath($dom);
        $nodes = $xPath->query($query);


        foreach ($nodes as $attributeNode) {
            /**
             * @var \DOMElement $attributeNode
             */

            $attributeName = (string)$attributeNode->getAttribute($this->attributeAttributeName);
            $this->setVariable($attributeName, $attributeNode->textContent);
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