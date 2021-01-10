<?php

namespace SoosyzeCore\User\Controller;

class UserApi extends \Soosyze\Controller
{
    public function select($req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $data = self::query()
            ->from('user')
            ->fetchAll();

        $out = [];
        foreach ($data as $value) {
            $out[] = [
                'id'   => $value[ 'user_id' ],
                'text' => t($value[ 'username' ])
            ];
        }

        return $this->json(200, [ 'results' => $out ]);
    }
}
