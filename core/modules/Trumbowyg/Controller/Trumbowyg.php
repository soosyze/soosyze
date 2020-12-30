<?php

namespace SoosyzeCore\Trumbowyg\Controller;

use Soosyze\Components\Validator\Validator;

class Trumbowyg extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
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

        $validator = (new Validator())
            ->addRule('image', 'image|max:1Mb')
            ->setInputs($files);

        if ($validator->isValid()) {
            $data = [
                'success' => true,
                'status'  => 200
            ];
            self::file()
                ->add($validator->getInput('image'))
                ->setPath('/upload')
                ->isResolvePath()
                ->isResolveName()
                ->callMove(function ($name, $fileName, $move) use (&$data) {
                    $data['link'] = '/' . $move;
                })
                ->saveOne();
        } else {
            $data = [
                'message' => 'uploadError',
                'status'  => 400
            ];
        }

        return $this->json($data[ 'status' ], $data);
    }
}
