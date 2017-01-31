<?php
/**
 * Created by PhpStorm.
 * User: danik
 * Date: 26/02/16
 * Time: 13:09
 */

namespace Nittro\Bridges\NittroLatte;
use Latte\CompileException;
use Latte\Compiler,
    Latte\Macros\MacroSet;
use Latte\MacroNode;
use Latte\MacroTokens;
use Latte\PhpWriter;


class NittroMacros extends MacroSet {

    public static function install(Compiler $compiler)
    {
        $me = new static($compiler);
        $me->addMacro('snippetId', 'echo %escape($_control->getSnippetId(%node.word))');
        $me->addMacro('param', 'echo %escape($_control->getParameterId(%node.word))');
        $me->addMacro('dynamic', null, null, 'echo Nette\Utils\Html::el()->setClass("nittro-snippet-container")->data("dynamic-mask", $_control->getSnippetId(%node.word))->attributes()');
        $me->addMacro('errors', [$me, 'macroErrors'], [$me, 'macroErrorsEnd'], null, self::AUTO_EMPTY);
    }


    public function macroErrors(MacroNode $node, PhpWriter $writer) {
        if ($node->modifiers) {
            throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
        } else if ($node->prefix) {
            if ($node->prefix !== MacroNode::PREFIX_NONE) {
                throw new CompileException('Unknown attribute ' . $node->getNotation() . ', use n:' . $node->name);
            } else if ($node->innerContent) {
                throw new CompileException('Unexpected content in ' . $node->getNotation() . ', tag must be empty');
            } else if (isset($node->htmlNode->attrs['id'])) {
                throw new CompileException('Cannot combine HTML attribute id with ' . $node->getNotation());
            }
        }
    }

    public function macroErrorsEnd(MacroNode $node, PhpWriter $writer) {
        $name = $node->tokenizer->fetchWord();
        $tagName = $node->prefix ? strtolower($node->htmlNode->name) : 'ul';
        $childName = in_array($tagName, ['ul', 'ol'], true) ? 'li' : 'p';

        $prefix = $writer->write(
            '$_input = ' . ($name[0] === '$' ? 'is_object(%0.word) ? %0.word : ' : '') . 'end($this->global->formsStack)[%0.word];'
            . ' $_el = Nette\Utils\Html::el(%1.var)->setId($_input->getHtmlId() . \'-errors\')'
            . ($node->tokenizer->isNext() ? '->addAttributes(%node.array);' : ';')
            . ' foreach($_input->getErrors() as $_e) $_el->create(%2.var)->setText($_e)',
            $name,
            $tagName,
            $childName
        );

        if ($node->prefix) {
            $node->openingCode = '<?php ' . $prefix . ' ?>';
            $node->attrCode = '<?php echo $_el->attributes(); ?>';
            $node->innerContent = '<?php echo $_el->getHtml() ?>';
        } else {
            $node->replaced = true;
            return $prefix . '; echo $_el';
        }
    }
}
