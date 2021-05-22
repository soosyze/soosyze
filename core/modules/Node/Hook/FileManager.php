<?php

namespace SoosyzeCore\Node\Hook;

use Soosyze\Components\Form\FormBuilder;

class FileManager
{
    /**
     * @var \Soosyze\App
     */
    private $core;

    /**
     * @var bool
     */
    private $hasFileManager;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    public function __construct($core, $module, $router)
    {
        $this->core   = $core;
        $this->router = $router;

        $this->hasFileManager = $module->has('FileManager');
    }

    public function hookNodeCreateForm(FormBuilder &$form, $content, $type)
    {
        if (!$this->hasFileManager) {
            return;
        }

        $form->append('fields-fieldset', function ($form) {
            $form->group('files-group', 'div', function ($form) {
                $response = '<div class="col-md-12">'
                    . '<div class="alert alert-info">'
                    . t('NOTE: You cannot add media files until you save the content. Just click Save')
                    . '</div>'
                    . '</div>';

                $form->legend('files-manager-label', t('Media'))
                    ->html('files-manager', '<div:attr>:content</div>', [
                        ':content' => $response,
                        'class'    => 'row',
                        'id'       => 'filemanager'
                ]);
            });
        });
    }

    public function hookNodeEditForm(FormBuilder &$form, $content)
    {
        if (!$this->hasFileManager) {
            return;
        }

        $request = $this->router->getRequestByRoute('filemanager.show', [
            ':path' => "/node/{$content[ 'type' ]}/{$content[ 'id' ]}"
        ]);
        $this->getFileManager($form, $request);
    }

    public function hookEntityForm(FormBuilder &$form, $content, $node, $entity)
    {
        if (!$this->hasFileManager) {
            return;
        }

        $request = $this->router->getRequestByRoute('filemanager.show', [
            ':path' => "/node/{$node[ 'type' ]}/{$node[ 'id' ]}/$entity"
        ]);
        $this->getFileManager($form, $request);
    }

    private function getFileManager(FormBuilder &$form, $request)
    {
        $response = '<div class="col-md-12">'
            . '<div class="alert alert-info">'
            . t('You do not have the necessary permissions to use the file manager')
            . '</div>'
            . '</div>';

        if ($this->core->callHook('app.granted.request', [ $request ])) {
            $route    = $this->router->parse($request);
            $response = $this->router->execute($route, $request);
        }

        $form->append('fields-fieldset', function ($form) use ($response) {
            $form->group('files-group', 'div', function ($form) use ($response) {
                $form->legend('files-manager-label', t('Media'))
                    ->html('files-manager', '<div:attr>:content</div>', [
                    ':content' => $response,
                    'class'    => 'row filemanager'
                ]);
            });
        });
    }
}
