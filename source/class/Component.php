<?php

namespace PHPComponent;



use PHPComponent\Traits\MustacheTemplate;
use PHPComponent\Traits\Collection;

class Component extends Template
{


    protected $attributeXPathQuery='//meta[@data-attribute-name]';
    protected $attributeAttributeName='data-attribute-name';




    public function __construct($template=null) {
        parent::__construct($template);
    }

    public function loadFromDOMNode(\DOMElement $node) {


        $this->dom=$this->createDomDocumentFromNode($node);
        $this->extractParametersFromDOM($this->dom);
        return $this;
    }

    public function extractParametersFromDOM($dom) {

        $query=$this->attributeXPathQuery;

        $xPath=new \DOMXPath($dom);
        $nodes=$xPath->query($query);


        foreach ($nodes as $attributeNode) {
            /**
             * @var \DOMElement $attributeNode
             */

            $attributeName=(string) $attributeNode->getAttribute($this->attributeAttributeName);
            $this->setVariable($attributeName, $attributeNode->textContent);

            /*
            echo '<pre id="' . __FILE__ . '-' . __LINE__ . '" style="border: solid 1px rgb(255,0,0); background-color:rgb(255,255,255)">';
            echo '<div style="background-color:rgba(100,100,100,1); color: rgba(255,255,255,1)">' . __FILE__ . '@' . __LINE__ . '</div>';
            print_r($dom->saveHTML());
            echo '</pre>';

            echo '<pre id="' . __FILE__ . '-' . __LINE__ . '" style="border: solid 1px rgb(255,0,0); background-color:rgb(255,255,255)">';
            echo '<div style="background-color:rgba(100,100,100,1); color: rgba(255,255,255,1)">' . __FILE__ . '@' . __LINE__ . '</div>';
            print_r($attributeNode);
            echo '</pre>';

            echo '<pre id="' . __FILE__ . '-' . __LINE__ . '" style="border: solid 1px rgb(255,0,0); background-color:rgb(255,255,255)">';
            echo '<div style="background-color:rgba(100,100,100,1); color: rgba(255,255,255,1)">' . __FILE__ . '@' . __LINE__ . '</div>';
            print_r($dom->innerHTML($attributeNode));
            echo '</pre>';
            die('EXIT '.__FILE__.'@'.__LINE__);
            $this->setVariable($attributeName, $dom->innerHTML($attributeNode));
            */




        }
    }


	public function bindAttributesValues($attributesValues) {


		foreach ($this->getVariables() as $variableName =>  $value) {

			$buffer=$value
			;
			preg_replace_callback('`\{\{\{(.*?)\}\}\}`', function($matches) use ($variableName, $attributesValues) {

				$variables=explode('.', $matches[1]);

				$currentValue=null;

				if(isset($attributesValues[$variables[0]])) {

					$currentValue=$attributesValues[$variables[0]];


					array_shift($variables);

					foreach ($variables as $subVariable) {
						if(is_array($currentValue) && isset($currentValue[$subVariable])) {
							$currentValue=$currentValue[$subVariable];
						}
						else if(is_object($currentValue) && isset($currentValue->$subVariable)) {
							$currentValue=$currentValue->$subVariable;
						}
						else {
							$currentValue=null;
							break;
						}
					}
				}
				$this->setVariable($variableName, $currentValue);
			}, $buffer);
		}

		return $this;


	}





    public function __toString() {
        return $this->getOutput();
    }


}