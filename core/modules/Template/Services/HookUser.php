<?php

namespace SoosyzeCore\Template\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Template' ][ 'template.admin' ] = t('Use the admin theme');
    }
}
