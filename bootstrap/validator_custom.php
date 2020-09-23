<?php

class RouteValue extends \Soosyze\Components\Validator\Rule
{
    protected function test($key, $value, $arg, $not = true)
    {
        /**
         * @var \Soosyze\App
         */
        $app = \Core::getInstance();

        $isRewrite  = $app->get('router')->isRewrite();
        $uri        = \Soosyze\Components\Http\Uri::create(($isRewrite
                ? ''
                : '?q=') . $value);
        $linkSource = $app->get('router')->parseQueryFromRequest(
            $app->getRequest()->withUri($uri)
        );

        $linkSource = $app->get('alias')->getSource($linkSource, $linkSource);

        $uriSource = \Soosyze\Components\Http\Uri::create($linkSource);
        $uriSource = $uriSource->withQuery('q=' . $uriSource->getPath());

        $isRoute = $app->get('router')->parse(
            $app->getRequest()
                ->withUri($uriSource)
                ->withMethod('get')
        );

        if (!$isRoute && $not) {
            $this->addReturn($key, 'must');
        }
    }

    protected function messages()
    {
        return [
            'must' => 'The value of :label must be a route.',
            'not'  => 'The value of :label should not be a route.'
        ];
    }
}

class RouteOrUrlValue extends \RouteValue
{
    protected function test($key, $value, $arg, $not = true)
    {
        $isRoute = !(new \RouteValue)
            ->hydrate('route', $key, $arg, $not)
            ->execute($value)
            ->hasErrors();
        $isLink = !(new \Soosyze\Components\Validator\Rules\Url)
            ->hydrate('url', $key, $arg, $not)
            ->execute($value)
            ->hasErrors();
        
        if (!($isRoute || $isLink) && $not) {
            $this->addReturn($key, 'must');
        }
    }

    protected function messages()
    {
        return [
            'must' => 'The value of :label must be a link or route.',
            'not'  => 'The value of :label should not be a link or route.'
        ];
    }
}

Soosyze\Components\Validator\Validator::addTestGlobal('route', new \RouteValue());
Soosyze\Components\Validator\Validator::addTestGlobal('route_or_url', new \RouteOrUrlValue());
