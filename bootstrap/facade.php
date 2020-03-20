<?php

if ($timezone = \Core::getInstance()->get('config')->get('settings.timezone')) {
    date_default_timezone_set($timezone);
}

function t($str, $vars = [])
{
    return \Core::getInstance()->get('translate')->t($str, $vars);
}

class RouteValue extends \Soosyze\Components\Validator\Rule
{
    protected function test($key, $value, $arg, $not = true)
    {
        /**
         * @var \Soosyze\App
         */
        $app = \Core::getInstance();

        $query = $value === '/' || strpos($value, '/#') === 0
            ? $app->get('config')->get('settings.path_index', '/')
            : $value;

        $parse = parse_url("?q=$query");
        $uri   = $app->getRequest()->getUri();
        if (!empty($parse[ 'query' ])) {
            $uri = $uri->withQuery($parse[ 'query' ]);
        } elseif (!empty($parse[ 'fragment' ])) {
            $uri = $uri->withFragment($parse[ 'fragment' ]);
        }

        $isRoute = $app->get('router')->parse(
            $app->getRequest()
                ->withUri($uri)
                ->withMethod('get')
        );

        if (!$isRoute && $not) {
            $this->addReturn($key, 'must');
        } elseif ($isRoute && !$not) {
            $this->addReturn($key, 'not');
        }
    }

    protected function messages()
    {
        return [
            'must' => 'The value of :label must be a link.',
            'not'  => 'The value of :label should not be a link.'
        ];
    }
}

Soosyze\Components\Validator\Validator::addTestGlobal('route', new \RouteValue());
