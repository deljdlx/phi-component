<?php

namespace Phi\Component;


use Phi\Component\Traits\MustacheTemplate;
use Phi\Component\Traits\Collection;
use Phi\Component\Interfaces\Renderer;

class Template implements Renderer
{


    use Collection;
    use MustacheTemplate;


    protected $output;
    protected $template;


    protected $renderer;
    protected static $staticRenderer;


    protected $libXMLFlag;
    protected $dom;
    protected $rootNode;


    protected $customTags = array();

    protected $componentEnabled = false;

    protected $defaultComponentTagName = 'phi-component';
    protected $componantClassNameAttributeName = 'data-instanceof';

    protected $components = array();


    public function __construct($template = null)
    {
        $this->libXMLFlag =
            \LIBXML_HTML_NOIMPLIED
            | \LIBXML_HTML_NODEFDTD
            | \LIBXML_NOXMLDECL
            | \LIBXML_NOENT
            | \LIBXML_NOERROR
            | \LIBXML_NOWARNING
            | \LIBXML_ERR_NONE;

        $this->template = $template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }


    public function registerCustomTag($tagName, $callback)
    {

        $customTagCallback = new CustomTag($tagName, $callback);
        $this->customTags[$tagName] = $customTagCallback;

        return $customTagCallback;
    }


    public function registerComponent($tagName, $componentName)
    {

        $this->registerCustomTag($tagName, function ($content, $node) use ($componentName) {
            if (!class_exists($componentName)) {
                throw new \LogicException('Class "' . $componentName . '" does not exist');
            }
            $component = new $componentName;
            $component->loadFromDOMNode($node);
            $buffer = $component->render();

            $this->components[] = $component;
            return $buffer;
        });
    }


    /**
     * @return Component[]
     */
    public function getComponents()
    {
        return $this->components;
    }


    public function enableComponents($value = true)
    {
        $this->componentEnabled = $value;
        return $this;
    }


    public function createDomDocumentFromNode(\DOMElement $node)
    {

        $valueNode = $node->cloneNode(true);

        $valueDocument = new DOMDocument('1.0', 'utf-8');
        $importedValueNode = $valueDocument->importNode($valueNode, true);
        $valueDocument->appendChild($importedValueNode);

        return $valueDocument;

    }


    public function parseDOM($buffer)
    {


        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'utf-8');

        $dom->substituteEntities=false;
        $dom->preserveWhiteSpace=false;
        $dom->formatOutput=true;
        $dom->xmlStandalone=true;

        $dom->loadHTML(mb_convert_encoding($buffer, 'HTML-ENTITIES', 'UTF-8'), $this->libXMLFlag);
        libxml_clear_errors();


        $this->rootNode = $dom->firstChild;

        if ($this->componentEnabled) {
            $this->initializeComponentParsing();
        }


        foreach ($this->customTags as $tagName => $customTag) {

            $query = '//' . $tagName;

            //$query = '//' . $tagName.'//*[not('.$tagName.')]';
            //$query = '//*[not('.$tagName.')]'.'//' . $tagName;
            //BBB//*[not(BBB)]

            $xPath = new \DOMXPath($dom);
            $nodes = $xPath->query($query);

            foreach ($nodes as $node) {

                /*
                echo '<pre id="' . __FILE__ . '-' . __LINE__ . '" style="border: solid 1px rgb(255,0,0); background-color:rgb(255,255,255)">';
                echo '<div style="background-color:rgba(100,100,100,1); color: rgba(255,255,255,1)">' . __FILE__ . '@' . __LINE__ . '</div>';
                echo get_class($this).' : ';
                print_r($tagName);
                echo '</pre>';
                */


                $nodeContent = $dom->innerXML($node);

                $content = call_user_func_array(array($customTag, 'render'), array($nodeContent, $node));

                $dom->replaceNodeWithContent($node, $content);
            }
        }

        $output=$dom->saveHTML();


        //"bug" with saveHTML and "src" attribute
        //replacing {{{  }}}} by %7B%7B%7B    %7D%7D%7D
        $output=urldecode($output);
        return $output;


    }


    public function initializeComponentParsing()
    {

        $template = $this;

        $this->registerCustomTag($this->defaultComponentTagName, function ($nodeContent, \DOMElement $node) use ($template) {

            $className = (string)$node->getAttribute($this->componantClassNameAttributeName);

            if (class_exists($className)) {


                $component = new $className;
                $component->loadFromDOMNode($node);
                $component->bindAttributesValues($this->getVariables());

                return $component;
            }

            return '';
        });
    }


    //=======================================================
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    public static function setStaticRenderer($renderer)
    {
        static::$staticRenderer = $renderer;
        return static::$staticRenderer;
    }

    //=======================================================


    /**
     * @param null $template
     * @param null $values
     * @param null $renderer
     * @return string
     */
    public function render()
    {



        $compiledDom = $this->parseDOM($this->template, true);
        $output = $this->compileMustache($compiledDom, $this->getVariables());
        $this->output = $this->doAfterRendering($output);
        return $this->output;
    }

    public function initializeRendering($template = null, $values = null, $renderer = null)
    {
        if ($renderer) {
            $this->renderer = $renderer;
        }

        if ($template) {
            $this->template = $template;
        }

        if (count($values)) {
            $this->setVariables($values);
        }
    }


    public function doAfterRendering($buffer)
    {

        if (is_callable(static::$staticRenderer)) {
            $buffer = call_user_func_array(static::$staticRenderer, array($buffer, $this));
        }

        if (is_callable($this->renderer)) {
            $buffer = call_user_func_array($this->renderer, array($buffer, $this));
        }
        return $buffer;
    }


    /**
     * @param null $template
     * @param null $values
     * @return string
     */
    public function getOutput($template = null, $values = null, $renderer = null)
    {
        if ($this->output === null) {
            return $this->render($template, $values, $renderer);
        } else {
            return $this->output;
        }
    }


    public function includeTemplate($file)
    {
        if (!is_file($file)) {
            throw new \LogicException('Template "' . $file . '" does not exist');
        }
        ob_start();
        include($file);
        return ob_get_clean();
    }

}
