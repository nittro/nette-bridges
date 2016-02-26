<?php
/**
 * Created by PhpStorm.
 * User: danik
 * Date: 26/02/16
 * Time: 13:09
 */

namespace Nittro\Nette\Bridges\NittroLatte;
use Latte\Compiler,
    Latte\Macros\MacroSet;


class NittroMacros extends MacroSet {

    public static function install(Compiler $compiler)
    {
        $me = new static($compiler);
        $me->addMacro('snippetId', 'echo %escape($_control->getSnippetId(%node.word))');
        $me->addMacro('param', 'echo %escape($_control->getParameterId(%node.word))');
        $me->addMacro('dynamic', null, null, 'echo Nette\Utils\Html::el()->setClass("snippet-container")->data("dynamic-mask", $_control->getSnippetId(%node.word))->attributes()');
    }
}
