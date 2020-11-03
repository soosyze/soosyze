<?php

namespace SoosyzeCore\Menu\Controller;

class MenuApi extends \Soosyze\Controller
{
    public function show($name, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        return $this->json(200, self::menu()->renderMenuSelect($name));
    }
}
