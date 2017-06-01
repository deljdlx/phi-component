<?php

namespace Phi\Component;


use Phi\Component\DOMDocument;
use Phi\Component\Traits\MustacheTemplate;
use Phi\Component\Traits\Collection;

class Page extends Component
{


    protected $javascriptFiles = null;
    protected $cssFiles = null;


    public function render()
    {



        $compiledDom = $this->parseDOM($this->template, true);
        $output = $this->compileMustache($compiledDom, $this->getVariables());


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
        $output = preg_replace('`</head>`i', '' . $cssBuffer . '</head>', $buffer);
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

            $globalJavascriptComponents = array_merge($globalJavascriptComponents, $component->getGlobalJavascripts(false));
            $globalJavascriptBuffer='';
            foreach ($globalJavascriptComponents as $descriptor) {
                if($descriptor['isURL']) {
                    $globalJavascriptBuffer.='<script src="'.$descriptor['declaration'].'"></script>';
                }
                else {
                    $globalJavascriptBuffer.='<script>
                    '.$descriptor['declaration'].'
                </script>';
                }
            }

            /*
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
            */
        }


        //

        $output = str_replace('</body>', $globalJavascriptBuffer . '</body>', $buffer);
        $output = str_replace('</body>', $javascriptBuffer . '</body>', $output);
        //$output = mb_ereg_replace('</body>', $globalJavascriptBuffer . '</body>', $buffer);
        //$output = preg_replace('`</body>`iu', $globalJavascriptBuffer . '</body>', $buffer);




        //echo $globalJavascriptBuffer;
        //echo $output;
        //die('EXIT '.__FILE__.'@'.__LINE__);





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
