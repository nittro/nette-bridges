<?php

namespace Nittro\Bridges\NittroLatte;

use Latte\Macros\MacroSet,
    Latte\Compiler,
    Latte\CompileException,
    Latte\MacroNode,
    Latte\PhpWriter;


class NittroMacros extends MacroSet {

    public static function install(Compiler $compiler)
    {
        $me = new static($compiler);
        $me->addMacro('snippetId', 'echo %escape($this->global->snippetDriver->getHtmlId(%node.word))');
        $me->addMacro('param', 'echo %escape($this->global->uiControl->getParameterId(%node.word))');
        $me->addMacro('flashes', [$me, 'validateMacro'], [$me, 'macroFlashes'], null, self::AUTO_EMPTY);
        $me->addMacro('flashTarget', null, null, [$me, 'macroFlashTarget']);
        $me->addMacro('dynamic', null, null, [$me, 'macroDynamic']);
        $me->addMacro('errors', [$me, 'validateMacro'], [$me, 'macroErrors'], null, self::AUTO_EMPTY);
        $me->addMacro('formErrors', [$me, 'validateMacro'], [$me, 'macroErrors'], null, self::AUTO_EMPTY);
        $me->addMacro('inputId', [$me, 'macroInputId']);
    }


    public function macroFlashes(MacroNode $node, PhpWriter $writer) {
        $tagName = $node->prefix ? strtolower($node->htmlNode->name) : 'ul';
        $childName = in_array($tagName, ['ul', 'ol'], true) ? 'li' : 'p';
        $classes = 'nittro-flash nittro-flash-inline nittro-flash-%type%';

        $prefix = '$_tmp = Nette\Utils\Html::el(%0.var)->setId($this->global->uiControl->getParameterId(\'flashes\'))->data(\'flash-inline\', true)'
            . ($node->tokenizer->isNext() ? '->addAttributes(%node.array);' : ';')
            . ' foreach($flashes as $_tmp2) $_tmp->create(%1.var)->setClass(str_replace(\'%type%\', $_tmp2->type, %2.var))->setText($_tmp2->message)';

        if ($node->prefix) {
            if (!empty($node->htmlNode->attrs['data-flash-classes'])) {
                $classes .= ' ' . $node->htmlNode->attrs['data-flash-classes'];
            }

            $node->openingCode = '<?php ' . $writer->write($prefix, $tagName, $childName, $classes) . ' ?>';
            $node->attrCode = '<?php echo $_tmp->attributes(); ?>';
            $node->innerContent = '<?php echo $_tmp->getHtml() ?>';
        } else {
            $node->replaced = true;
            $prefix .= '; echo $_tmp';
            return $writer->write($prefix, $tagName, $childName, $classes);
        }
    }

    public function macroFlashTarget(MacroNode $node, PhpWriter $writer) {
        if ($node->modifiers) {
            throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
        } else if ($node->prefix !== MacroNode::PREFIX_NONE) {
            throw new CompileException('Unknown macro ' . $node->getNotation() . ', did you mean n:' . $node->name . '?');
        } else if (!empty($node->htmlNode->attrs['id'])) {
            throw new CompileException('Cannot combine HTML attribute id with ' . $node->getNotation());
        }

        $attrCode = 'echo \' id="\' . htmlSpecialChars($this->global->uiControl->getParameterId(\'flashes\')) . \'"\'';

        if ($node->tokenizer->isNext()) {
            $attrCode .= '; echo \' data-flash-placement="\' . %node.word . \'"\'';
        }

        $node->attrCode = $writer->write("<?php $attrCode ?>");
    }


    public function macroDynamic(MacroNode $node, PhpWriter $writer) {
        if (!$node->prefix || $node->prefix !== MacroNode::PREFIX_NONE) {
            throw new CompileException('Unknown macro ' . $node->getNotation() . ', did you mean n:' . $node->name . '?');
        }

        $attrCode = 'echo \' data-dynamic-mask="\' . htmlSpecialChars($this->global->snippetDriver->getHtmlId(%node.word)) . \'"\'';

        if (!empty($node->htmlNode->attrs['class'])) {
            if (!preg_match('/(?:^|\s)nittro-snippet-container(?:\s|$)/', $node->htmlNode->attrs['class'])) {
                throw new CompileException('Dynamic container specifying the "class" attribute must include the "nittro-snippet-container" class');
            }
        } else {
            $attrCode .= ' . \' class="nittro-snippet-container"\'';
        }

        $node->attrCode = $writer->write("<?php $attrCode ?>");
    }

    public function macroErrors(MacroNode $node, PhpWriter $writer) {
        $words = $node->tokenizer->fetchWords();
        $name = array_shift($words);
        $tagName = $node->prefix ? strtolower($node->htmlNode->name) : 'ul';
        $childName = in_array($tagName, ['ul', 'ol'], true) ? 'li' : 'p';

        if ($node->name === 'formErrors') {
            $prefix = $writer->write(
                '$_tmp = ' . ($name && $name[0] === '$' ? 'is_object(%0.word) ? %0.word : ' : '')
                . ($name ? '$this->global->uiControl[%0.word];' : 'end($this->global->formsStack);')
                . ' $_tmp2 = Nette\Utils\Html::el(%1.var)->setId($_tmp->getElementPrototype()->id . \'-errors\')'
                . ($node->tokenizer->isNext() ? '->addAttributes(%node.array);' : ';')
                . ' foreach($_tmp->getOwnErrors() as $_e) $_tmp2->create(%2.var)->setClass(\'error\')->setText($_e)',
                $name,
                $tagName,
                $childName
            );
        } else {
            if (!$name) {
                throw new CompileException('Missing input name in ' . $node->getNotation());
            }

            $prefix = $writer->write(
                '$_tmp = ' . ($name[0] === '$' ? 'is_object(%0.word) ? %0.word : ' : '')
                . 'end($this->global->formsStack)[%0.word];'
                . ' $_tmp2 = Nette\Utils\Html::el(%2.var)->setId($_tmp->%1.raw . \'-errors\')'
                . ($node->tokenizer->isNext() ? '->addAttributes(%node.array);' : ';')
                . ' foreach($_tmp->getErrors() as $_e) $_tmp2->create(%3.var)->setClass(\'error\')->setText($_e)',
                $name,
                $words ? 'getControlPart(' . implode(', ', array_map([$writer, 'formatWord'], $words)) . ')->getAttribute(\'id\')' : 'getHtmlId()',
                $tagName,
                $childName
            );
        }

        if ($node->prefix) {
            $node->openingCode = '<?php ' . $prefix . ' ?>';
            $node->attrCode = '<?php echo $_tmp2->attributes(); ?>';
            $node->innerContent = '<?php echo $_tmp2->getHtml() ?>';
        } else {
            $node->replaced = true;
            return $prefix . '; echo $_tmp2';
        }
    }


    public function macroInputId(MacroNode $node, PhpWriter $writer) {
        $words = $node->tokenizer->fetchWords();
        $name = array_shift($words);

        return $writer->write(
            '$_tmp = ' . ($name[0] === '$' ? 'is_object(%0.word) ? %0.word : ' : '')
            . 'end($this->global->formsStack)[%0.word];'
            . ' echo %escape($_tmp->%1.raw)',
            $name,
            $words ? 'getControlPart(' . implode(', ', array_map([$writer, 'formatWord'], $words)) . ')->getAttribute(\'id\')' : 'getHtmlId()'
        );
    }


    public function validateMacro(MacroNode $node) {
        if ($node->modifiers) {
            throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
        } else if ($node->prefix) {
            if ($node->prefix !== MacroNode::PREFIX_NONE) {
                throw new CompileException('Unknown macro ' . $node->getNotation() . ', did you mean n:' . $node->name . '?');
            } else if ($node->innerContent) {
                throw new CompileException('Unexpected content in ' . $node->getNotation() . ', tag must be empty');
            } else if (isset($node->htmlNode->attrs['id'])) {
                throw new CompileException('Cannot combine HTML attribute id with ' . $node->getNotation());
            }
        }
    }
}
