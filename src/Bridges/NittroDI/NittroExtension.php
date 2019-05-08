<?php

declare(strict_types=1);

namespace Nittro\Bridges\NittroDI;

use Nette;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\Statement;
use Nittro\Bridges\NittroLatte\NittroMacros;
use Nittro\Bridges\NittroLatte\NittroRuntime;


class NittroExtension extends CompilerExtension {

    public $defaults = [
        'noconflict' => false,
    ];

    public function getConfigSchema() : Nette\Schema\Schema {
        return Nette\Schema\Expect::structure([
            'noconflict' => Nette\Schema\Expect::bool(false),
        ]);
    }

    public function beforeCompile() : void {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();

        if ($latte = $builder->getByType(ILatteFactory::class)) {
            $definition = $builder->getDefinition($latte);
            $service = $definition instanceof FactoryDefinition ? $definition->getResultDefinition() : $definition;

            $service
                ->addSetup('addProvider', [ 'nittro', new Statement(NittroRuntime::class) ])
                ->addSetup(
                    '?->onCompile[] = function ($engine) { ' . NittroMacros::class . '::install($engine->getCompiler(), ?); }', [
                    '@self',
                    $config->noconflict,
                ]);
        }
    }

}
