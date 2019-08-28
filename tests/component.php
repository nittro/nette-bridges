<?php

declare(strict_types=1);

require_once  __DIR__ . '/../vendor/autoload.php';


class TestComponent extends Nette\Application\UI\Control {
    use Nittro\Bridges\NittroUI\ComponentUtils;
}

$control = new TestComponent();
