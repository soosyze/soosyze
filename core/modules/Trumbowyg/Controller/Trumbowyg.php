<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Trumbowyg\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Validator\Validator;

/**
 * @method \Soosyze\Core\Modules\FileSystem\Services\File file()
 *
 * @phpstan-type Data array{
 *      link?: string,
 *      message: string,
 *      success?: bool,
 *      status: bool
 * }
 */
class Trumbowyg extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
    }

    public function upload(ServerRequestInterface $req): ResponseInterface
    {
        $validator = self::getValidator($req);
        /** @phpstan-var Data $data */
        $data = [
            'message' => 'uploadError',
            'status'  => 400
        ];

        if ($validator->isValid()) {
            $data = [
                'success' => true,
                'status'  => 200
            ];

            $this->saveFile($data, $validator);
        }

        return $this->json((int) $data[ 'status' ], $data);
    }

    private static function getValidator(ServerRequestInterface $req): Validator
    {
        return (new Validator())
                ->addRule('image', 'image|max:1Mb')
                ->setInputs($req->getUploadedFiles());
    }

    private function saveFile(array &$data, Validator $validator): void
    {
        /** @phpstan-var UploadedFileInterface $imageInput */
        $imageInput = $validator->getInput('image');
        self::file()
            ->add($imageInput)
            ->setPath('/upload')
            ->isResolvePath()
            ->isResolveName()
            ->callMove(static function (
                string $name,
                string $fileName,
                string $move
            ) use (&$data): void {
                $data[ 'link' ] = '/' . $move;
            })
            ->saveOne();
    }
}
