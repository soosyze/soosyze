<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/core/modules'
    ]);
    $parameters->set(Option::SKIP, [
        __DIR__ . '/core/modules/*/Assets/*',
        __DIR__ . '/core/modules/*/Config/*',
        __DIR__ . '/core/modules/*/Lang/*',
        __DIR__ . '/core/modules/*/Views/*',
   ]);

    // is your PHP version different from the one your refactor to? [default: your PHP version], uses PHP_VERSION_ID format
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);

    // Define what rule sets will be applied
    $containerConfigurator->import(SetList::CODE_QUALITY);
};
