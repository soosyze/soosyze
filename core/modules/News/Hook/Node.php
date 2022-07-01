<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\News\Hook;

use Soosyze\Components\Validator\Validator;
use Soosyze\Core\Modules\News\Hook\Config;
use Soosyze\Core\Modules\Template\Services\Templating;

class Node
{
    public const NODE_TYPE = 'article';

    /**
     * @var string
     */
    private $newDefaultImage;

    /**
     * @var string
     */
    private $newDefaultIcon;

    public function __construct(
        string $newDefaultImage = Config::DEFAULT_IMAGE,
        string $newDefaultIcon = Config::DEFAULT_ICON
    ) {
        $this->newDefaultImage = $newDefaultImage;
        $this->newDefaultIcon  = $newDefaultIcon;
    }

    public function hookNodeShowTpl(Templating $tpl, array $node, int $idNode): void
    {
        if ($node[ 'type' ] === self::NODE_TYPE) {
            $tpl->getBlock('page.content')->addPathOverride(dirname(__DIR__) . '/Views/');
        }
    }

    public function hookNodeStoreBefore(
        Validator $validator,
        array &$fieldsInsert,
        string $type
    ): void {
        if ($type === self::NODE_TYPE) {
            $words = str_word_count(strip_tags($fieldsInsert[ 'body' ]));
            $readingTime = ceil($words / 200);
            $fieldsInsert[ 'reading_time' ] = $readingTime == 0 ? 1 : $readingTime;
        }
    }

    public function hookNodeUpdateBefore(
        Validator $validator,
        array &$fieldsUpdate,
        array $node,
        int $idNode
    ): void {
        if ($node[ 'type' ] === self::NODE_TYPE) {
            $words = str_word_count(strip_tags($fieldsUpdate[ 'body' ]));

            $readingTime = ceil($words / 200);
            $fieldsUpdate[ 'reading_time' ] = $readingTime == 0 ? 1 : $readingTime;
        }
    }

    public function hookNodeMakefields(string $type, array &$fields, array &$data): void
    {
        if ($type === self::NODE_TYPE && empty($data[ 'image' ])) {
            $fields[] = [
                'field_name' => 'icon',
                'field_type' => 'text'
            ];

            $data[ 'image' ] = $this->newDefaultImage;
            $data[ 'icon' ]  = $this->newDefaultIcon;
        }
    }

    public function hookNodeFormData(array &$content, string $type): void
    {
        if ($type === self::NODE_TYPE && empty($content[ 'image' ])) {
            $content[ 'image' ] = $this->newDefaultImage;
        }
    }
}
