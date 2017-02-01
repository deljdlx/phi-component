<?php

namespace PHPComponent;



class DOMDocument extends \DOMDocument
{



    public function replaceNodeWithContent($containerNode, $content) {
        //$contentNode=$this->createCDATASection($content);
        $contentNode=$this->createDocumentFragment();
        $contentNode->appendXML($content);





        $this->replaceNodeWithNode($containerNode, $contentNode);

        return $contentNode;
    }


    protected function replaceNodeWithNodes($containerNode, $nodes) {


        $nodesArray=array();
        foreach($nodes as $node){
            $nodesArray[] = $node;
        }



        $reversedValues=array();

        //inversion de l'ordre des noeud à injecter (car par là suite on utilse un "insert before")
        $index=0;
        foreach ($nodesArray as $valueNode) {
            $reversedValues[]=$nodesArray[$nodes->length-1-$index];
            $index++;
        }

        //réinjection des noeuds dans le document principal
        $index=0;
        foreach ($reversedValues as $valueNode) {
            $newValueNode=$valueNode->cloneNode(true);


            $importedValueNode=$this->importNode($newValueNode, true);

            if($index==0) {

                $containerNode->parentNode->replaceChild($importedValueNode, $containerNode);
                $containerNode=$importedValueNode;
            }
            else {
                $containerNode->parentNode->insertBefore($importedValueNode, $containerNode);
                $containerNode=$importedValueNode;
            }

            $index++;
        }
    }


    protected function replaceNodeWithNode($containerNode, $node) {

        $newValueNode=$node->cloneNode(true);
        $importedValueNode=$this->importNode($newValueNode, true);

        $containerNode->parentNode->replaceChild($importedValueNode, $containerNode);
    }

    public function getXML($node=null) {
        if(!$node) {
            return $this->saveXML($this->firstChild);
        }
        else {
            return $this->saveXML($node);
        }
    }



    public function innerXML(\DOMNode $element) {
        $innerHTML = "";
        $children  = $element->childNodes;

        foreach ($children as $child)
        {
            $innerHTML .= $element->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }

    public function innerHTML(\DOMNode $element) {
        $innerHTML = "";
        $children  = $element->childNodes;

        foreach ($children as $child)
        {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return $innerHTML;
    }


    /*
    public function convertNodeToString($node) {
        $valueDocument=new DOMDocument('1.0', 'utf-8');
        $clone=$node->cloneNode(true);
        $importedNode=$valueDocument->importNode($clone, true);
        $valueDocument->appendChild($importedNode);
        $html=$valueDocument->saveHTML();
        return $html;
    }
    */


}