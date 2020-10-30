<?php

namespace SoosyzeCore\Config\Services;

interface ConfigInterface
{
    public function defaultValues();

    public function menu(&$menu);

    public function form(&$form, $data, $req);

    public function validator(&$validator);

    public function files(&$inputsFile);

    public function before(&$validator, &$data, $id);

    public function after(&$validator, $data, $id);
}
