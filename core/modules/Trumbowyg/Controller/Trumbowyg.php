<?php

namespace SoosyzeCore\Trumbowyg\Controller;

use Soosyze\Components\Validator\Validator;

class Trumbowyg extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
    }

    public function upload($req)
    {
        if (!$req->isAjax()) {
            $post = $req->getParsedBody();

            return $this->json(405, [
                    'message'  => 'uploadNotAjax',
                    'formData' => $post
            ]);
        }

        $files = $req->getUploadedFiles();

        $validator = (new Validator)
            ->addRule('image', 'image|max:1Mb')
            ->setInputs($files);

        if ($validator->isValid()) {
            $image = $validator->getInput('image');
            $path  = self::core()->getSettingEnv('files_public', 'app/files') . '/upload';
            $link  = self::file()
                ->add($image)
                ->setPath($path)
                ->setResolvePath()
                ->setResolveName()
                ->saveOne();

            $data = [
                'success' => true,
                'link'    => '/' . $link,
                'status'  => 200
            ];
        } else {
            $data = [
                'message' => 'uploadError',
                'status'  => 400
            ];
        }

        return $this->json($data[ 'status' ], $data);
    }
}
