<?php

namespace SoosyzeCore\User;

interface UserInterface
{
    public function hookUserPermissionModule(array &$permissions);
}
