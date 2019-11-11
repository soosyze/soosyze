<?php

namespace SoosyzeCore\System;

use Psr\Container\ContainerInterface;

interface Migration
{
    public function getDir();

    public function install(ContainerInterface $ci);
    
    public function seeders(ContainerInterface $ci);

    public function uninstall(ContainerInterface $ci);
    
    public function hookInstall(ContainerInterface $ci);
    
    public function hookUninstall(ContainerInterface $ci);
}
