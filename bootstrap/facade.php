<?php

function t($str, $vars = [])
{
    global $app;

    return $app->get('translate')->t($str, $vars);
}
