<?php







require(__DIR__.'/../bootstrap.php');







$template='<button>Composant bouton</button>';
$test=new \PHPComponent\Component($template);
echo $test->render();
echo '<hr/>';


$template='
    <button>{{{content}}}</button>
';
$test=new \PHPComponent\Component($template);
$test->setVariable('content', 'Contenu injecté');
echo $test->render();
echo '<hr/>';






$template='
    <button style="{{{style}}}">test attribut style</button>
';
$test=new \PHPComponent\Component($template);
$test->setVariable('style', 'background-color: #CFC');
echo $test->render();
echo '<hr/>';






$template='
    <button>Sous composant</button>
';
$subComponent=new \PHPComponent\Component($template);

$template='
    <div style="border: solid 3px #002a80; padding: 10px;">Composant container <div>{{{content}}}</div></div>
';
$component=new \PHPComponent\Component($template);
$component->setVariable('content', $subComponent);
echo $component->render();
echo '<hr/>';









$template='
    <div style="border: solid 3px #002a80; padding: 10px;">
        <div>{{{content}}}</div>
        <div>Injection de texte</div>
       <div>
            <phi-component></phi-component>
       </div>
    </div>
';

$test=new \PHPComponent\Component($template);
$test->setVariable('content', '::Variable mustache "content"::');
$test->registerCustomTag('phi-component', function() {
    return '{{{Contenu texte}}}';
});
echo $test->render();
echo '<hr/>';

//=======================================================


$template='
    <div style="border: solid 3px #002a80; padding: 10px;">
        <div>{{{content}}}</div>

        Composant container

       <div>
            <phi-component data-instanceof="TestComponent">

            </phi-component>
       </div>
    </div>
';

$test=new \PHPComponent\Template($template);
$test->registerCustomTag('phi-component', function() {
   return '<button>Custom tag phi-component</button>';
});
$test->setVariable('content', 'Contenu injecté');
echo $test->render();
echo '<hr/>';



//=======================================================


class TestComponent extends \PHPComponent\Component
{
    public function render($template=null, $values=null) {
        return $this->getVariable('content');
    }
}


$template='
    <div style="border: solid 3px #002a80; padding: 10px;">
        <div>{{{content}}}</div>

        Composant container avec composant

       <div>

            <phi-component data-instanceof="TestComponent">
                <meta  data-attribute-name="content"><![CDATA[
                    Test :
                    <span style="border: solid 5px #ffe57f">
                        hello
                    </span>
                    ]]>
                </meta>
            </phi-component>



       </div>
    </div>
';

$test=new \PHPComponent\Template($template);
$test->setVariable('content', 'Contenu injecté');
$test->enableComponents(true);

echo $test->render();
echo '<hr/>';

//=======================================================




class TestComponent2 extends \PHPComponent\Component
{
	public function render($template=null, $values=null) {
		return '<button>'.$this->getVariable('content').'</button>';
	}
}


$template='
    <div style="border: solid 3px #002a80; padding: 10px;">

        Composant container avec composant

       <div>

            <phi-component data-instanceof="TestComponent2">
                <meta  data-attribute-name="content">{{{content}}}</meta>
            </phi-component>
            
       </div>
    </div>
';

$test=new \PHPComponent\Template($template);
$test->setVariable('content', 'Contenu injecté dans le composant');
$test->enableComponents(true);

echo $test->render();
echo '<hr/>';


//=======================================================




class TestComponent3 extends \PHPComponent\Component
{
	public function render($template=null, $values=null) {
		return '<button>'.$this->getVariable('content').'</button>';
	}
}


$template='
    <div style="border: solid 3px #002a80; padding: 10px;">

        Composant container avec composant

       <div>

            <phi-component data-instanceof="TestComponent3">
                <meta  data-attribute-name="content">{{{content.test1.sub1}}}</meta>
            </phi-component>
            
       </div>
    </div>
';

$test=new \PHPComponent\Template($template);
$test->setVariable('content', array(
	'test1'=>array(
		'sub1'=>'hello',
		'sub2'=>'world'
	),
	'test2'=>'test 2',
));
$test->enableComponents(true);

echo $test->render();
echo '<hr/>';





class TestComponent4 extends \PHPComponent\Component
{
	public function render($template=null, $values=null) {
		return '<button>'.
			$this->getVariable('content')['test1']['sub1'].
			' '.$this->getVariable('content')['test1']['sub2'].
		'</button>';
	}
}


$template='
    <div style="border: solid 3px #002a80; padding: 10px;">

        Composant container avec composant data imbriquées

       <div>

            <phi-component data-instanceof="TestComponent4">
                <meta  data-attribute-name="content">{{{content}}}</meta>
            </phi-component>
            
       </div>
    </div>
';

$test=new \PHPComponent\Template($template);
$test->setVariable('content', array(
	'test1'=>array(
		'sub1'=>'hello',
		'sub2'=>'world'
	),
	'test2'=>'test 2',
));
$test->enableComponents(true);

echo $test->render();
echo '<hr/>';





//=======================================================
//=======================================================




$template='
    <div style="border: solid 3px #002a80; padding: 10px;">
        <div>{{{content}}}</div>
        <div>Injection de texte</div>
       <div>
            <yolo><button><strong class="yolo">click here</strong></button></yolo>
            <br/>
            <yolo2><strong>test ici</strong></yolo2>
       </div>
    </div>
';

$test=new \PHPComponent\Component($template);
$yoloTag=$test->registerCustomTag('yolo', function($nodeContent, $node) {
    return $nodeContent;
});
$yoloTag->addJavascript('
    document.querySelector(".yolo").onclick=function() {alert("click");};
');


$test->registerCustomTag('yolo2', function($nodeContent, $node) {
    return '<em>'.$nodeContent.'</em>';
});

echo $test->render();
echo '<hr/>';

//=======================================================




