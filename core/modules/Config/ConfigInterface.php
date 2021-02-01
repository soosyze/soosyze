<?php

namespace SoosyzeCore\Config;

interface ConfigInterface
{
    public function defaultValues();

    public function menu(array &$menu);

    public function form(&$form, array $data, $req);

    public function validator(&$validator);

    public function files(array &$inputsFile);

    public function before(&$validator, array &$data, $id);

    public function after(&$validator, array $data, $id);
}
