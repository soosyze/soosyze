<?php

namespace SoosyzeCore\Block\Services;

final class HookConfig implements \SoosyzeCore\Config\Services\ConfigInterface
{
    private $socials = [
        'blogger'    => 'Blogger',
        'codepen'    => 'Codepen',
        'discord'    => 'Discord',
        'dribbble'   => 'Dribbble',
        'facebook'   => 'Facebook',
        'github'     => 'GitHub',
        'gitlab'     => 'GitLab',
        'instagram'  => 'Instagram',
        'linkedin'   => 'Linkedin',
        'mastodon'   => 'Mastodon',
        'snapchat'   => 'Snapchat',
        'soundcloud' => 'Soundcloud',
        'spotify'    => 'Spotify',
        'steam'      => 'Steam',
        'tumblr'     => 'Tumblr',
        'twitch'     => 'Twitch',
        'twitter'    => 'Twitter',
        'youtube'    => 'Youtube'
    ];

    public function defaultValues()
    {
        $out = [];
        foreach (array_keys($this->socials) as $social) {
            $out['icon_socials'][$social] = '';
        }

        return $out;
    }

    public function menu(array &$menu)
    {
        $menu[ 'social' ] = [
            'title_link' => 'Social networks'
        ];
    }

    public function form(&$form, array $data, $req)
    {
        $form->group('social-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('social-legend', t('Social networks'));
            foreach ($this->socials as $key => $social) {
                $form->group("$key-group", 'div', function ($form) use ($data, $key, $social) {
                    $form->label("$key-label", $social, [ 'for' => $key ])
                        ->group("$key-flex", 'div', function ($form) use ($data, $key) {
                            $form->html("$key-icon", '<span:attr>:content</span>', [
                                ':content' => '<i class="fab fa-' . $key . '" aria-hidden="true"></i>'
                            ])
                            ->text($key, [
                                'class' => 'form-control',
                                'value' => $data['icon_socials'][ $key ]
                            ]);
                        }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group' ]);
            }
        });
    }

    public function validator(&$validator)
    {
        foreach (array_keys($this->socials) as $key) {
            $validator->addRule($key, '!required|route_or_url');
        }
        $validator->setLabels($this->socials);
    }

    public function before(&$validator, array &$data, $id)
    {
        $data[ 'icon_socials' ] = [];
        foreach (array_keys($this->socials) as $key) {
            if ($validator->getInput($key)) {
                $data[ 'icon_socials' ][ $key ] = $validator->getInput($key);
            }
        }
    }

    public function after(&$validator, array $data, $id)
    {
    }

    public function files(array &$inputsFile)
    {
    }
}
