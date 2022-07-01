<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\System;

/**
 * @phpstan-type ToolEntity array{
 *      description: string,
 *      icon: array{ name: string, background-color: string, color: string },
 *      link: \Psr\Http\Message\RequestInterface,
 *      title: string
 * }
 */
interface ToolInterface
{
    /**
     * @param array<ToolEntity> $tools
     */
    public function hookToolAdmin(array &$tools): void;
}
