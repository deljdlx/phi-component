<?php

namespace PHPComponent;


use PHPComponent\DOMDocument;
use PHPComponent\Traits\MustacheTemplate;
use PHPComponent\Traits\Collection;

class Page extends Template
{


    protected $javascriptFiles = null;
    protected $cssFiles = null;


    public function render($template = null, $values = null)
    {
        $output = parent::render($template, $values);
        $this->output = $this->injectCSS($output);
        $this->output = $this->injectJavascript($this->output);

        $this->extractJavascriptFiles();
        $this->extractCSSFiles();
        return $this->output;

    }

    public function injectCSS($buffer)
    {
        $cssBuffer = '';
        foreach ($this->getComponents() as $component) {
            $cssBuffer .= $component->getGlobalCSS();
        }
        $output = preg_replace('`</head>`i', '<style>' . $cssBuffer . '</style></head>', $buffer);
        return $output;
    }

    public function injectJavascript($buffer)
    {
        $globalJavascriptComponents = array();

        $globalJavascriptBuffer = '';
        $javascriptBuffer = '';
        foreach ($this->getComponents() as $component) {
            $script = $component->getJavascript();
            if ($script) {
                $javascriptBuffer .= '<script>' . $script . '</script>';
            }


            $componentClassName = get_class($component);
            if (!isset($globalJavascriptComponents[$componentClassName])) {
                $scripts = $component->getGlobalJavascripts();
                if (!empty($scripts)) {
                    foreach ($scripts as $name => $script) {
                        $globalJavascriptBuffer .= '<script>'."\n".'//' . $name . ' => ' . $componentClassName . "\n" . $script . '</script>'."\n";
                        $globalJavascriptComponents[$componentClassName] = true;
                    }
                }
            }
        }


        //$output = preg_replace('`</body>`iu', $globalJavascriptBuffer . '</body>', $buffer);

        $output = str_replace('</body>', $globalJavascriptBuffer . '</body>', $buffer);


        //echo $globalJavascriptBuffer;
        //echo $output;
        //die('EXIT '.__FILE__.'@'.__LINE__);

        $output = str_replace('</body>', $javascriptBuffer . '</body>', $output);



        return $output;
    }


    public function extractJavascriptFiles()
    {
        preg_match_all('`<script .*?src="(.*?)"`i', $this->output, $matches);
        $this->javascriptFiles = $matches[1];
        return $this->javascriptFiles;
    }

    public function getJavascriptFiles()
    {
        if ($this->javascriptFiles == null) {
            $this->extractJavascriptFiles();
        }
        return $this->javascriptFiles;
    }

    public function extractCSSFiles()
    {
        $this->cssFiles = array();
        preg_match_all('`<link( [^>]*?href="(.*?)".*?)>`i', $this->output, $matches);
        foreach ($matches[1] as $index => $linkTag) {
            if (stripos($linkTag, 'stylesheet')) {
                $this->cssFiles[] = $matches[2][$index];
            }
        }
        return $this->cssFiles;
    }

    public function getCSSFiles()
    {
        if ($this->cssFiles == null) {
            $this->extractCSSFiles();
        }
        return $this->cssFiles;
    }


}
