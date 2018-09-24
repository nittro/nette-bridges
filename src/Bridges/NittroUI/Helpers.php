<?php


namespace Nittro\Bridges\NittroUI;

use Nette\Application\UI\Component;
use Nette\StaticClass;


class Helpers {
    use StaticClass;

    public static function formatDialogId($name, Component $component = null) {
        return $name[0] !== '@'
            ? 'dlg-' . ($component ? $component->getUniqueId() : '') . '-' . $name
            : substr($name, 1);
    }

}
