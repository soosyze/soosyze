<?php

namespace SoosyzeCore\FileManager\Form;

use SoosyzeCore\FileManager\Services\FileManager;

class FormPermission extends \Soosyze\Components\Form\FormBuilder
{
    protected $values = [
        'folder_show'         => '',
        'folder_show_sub'     => true,
        'profil_weight'       => 1,
        'roles'               => [],
        'folder_store'        => true,
        'folder_update'       => false,
        'folder_delete'       => false,
        'folder_size'         => 10,
        'file_store'          => true,
        'file_update'         => true,
        'file_delete'         => false,
        'file_download'       => true,
        'file_clipboard'      => true,
        'file_size'           => 1,
        'file_extensions_all' => false,
        'file_extensions'     => []
    ];

    protected $roles = [];

    public function setValues(array $values)
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    public function roles(array $roles, array $useRole)
    {
        $this->roles = array_merge($this->roles, $roles);
        foreach ($useRole as $value) {
            $this->values[ 'roles' ][ $value ] = '';
        }

        return $this;
    }

    public function makeFields()
    {
        $this->group('folder_show-fieldset', 'fieldset', function ($form) {
            $form->legend('folder_show-legend', t('Directory'))
                ->group('folder_show-group', 'div', function ($form) {
                    $form->label('folder_show-label', t('Directory path'))
                    ->text('folder_show', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->values[ 'folder_show' ]
                    ])
                    ->html('cancel', '<p>:_content</p>', [
                        '_content' => t('Variables allowed') . ' <code>:user_id</code>'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('folder_show_sub-group', 'div', function ($form) {
                    $form->checkbox('folder_show_sub', [ 'checked' => $this->values[ 'folder_show_sub' ] ])
                    ->label('folder_show_sub-label', '<i class="ui"></i><i class="fa fa-sitemap"></i> ' . t('Sub directories included'), [
                        'for' => 'folder_show_sub' ]);
                }, [ 'class' => 'form-group' ])
                ->group('profil_weight-group', 'div', function ($form) {
                    $form
                    ->label('profil_weight-label', t('Weight'))
                    ->number('profil_weight', [
                        'class' => 'form-control',
                        'max'   => 50,
                        'min'   => 0,
                        'value' => $this->values[ 'profil_weight' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
        })
            ->group('roles-fieldset', 'fieldset', function ($form) {
                $form->legend('roles-legend', t('User Roles'));
                foreach ($this->roles as $role) {
                    $form->group('roles-' . $role[ 'role_id' ] . '-group', 'div', function ($form) use ($role) {
                        $form->checkbox("roles[{$role[ 'role_id' ]}]", [
                            'checked' => key_exists($role[ 'role_id' ], $this->values[ 'roles' ]),
                            'id'      => "role-{$role[ 'role_id' ]}",
                            'value'   => $role[ 'role_label' ]
                        ])
                        ->label(
                            'role-' . $role[ 'role_id' ] . '-label',
                            '<span class="ui"></span>'
                            . '<span class="badge-role" style="background-color: ' . $role[ 'role_color' ] . '">'
                            . '<i class="' . $role[ 'role_icon' ] . '" aria-hidden="true"></i>'
                            . '</span> '
                            . t($role[ 'role_label' ]),
                            [ 'for' => "role-{$role[ 'role_id' ]}" ]
                        );
                    }, [ 'class' => 'form-group' ]);
                }
            })
            ->group('folder-fieldset', 'fieldset', function ($form) {
                $form->legend('folder-legend', t('Directory permissions'))
                ->group('folder_store-group', 'div', function ($form) {
                    $form->checkbox('folder_store', [ 'checked' => $this->values[ 'folder_store' ] ])
                    ->label('folder_store-label', '<i class="ui"></i><i class="fa fa-plus"></i> ' . t('Create'), [
                        'for' => 'folder_store' ]);
                }, [ 'class' => 'form-group col-sm-4' ])
                ->group('folder_update-group', 'div', function ($form) {
                    $form->checkbox('folder_update', [ 'checked' => $this->values[ 'folder_update' ] ])
                    ->label('folder_update-label', '<i class="ui"></i><i class="fa fa-edit"></i> ' . t('Edit'), [
                        'for' => 'folder_update' ]);
                }, [ 'class' => 'form-group col-sm-4' ])
                ->group('folder_delete-group', 'div', function ($form) {
                    $form->checkbox('folder_delete', [ 'checked' => $this->values[ 'folder_delete' ] ])
                    ->label('folder_delete-label', '<i class="ui"></i><i class="fa fa-times"></i> ' . t('Delete'), [
                        'for' => 'folder_delete' ]);
                }, [ 'class' => 'form-group col-sm-4' ])
                ->group('folder_size-group', 'div', function ($form) {
                    $form->label('folder_size-label', t('Size limit by directory'), [
                        'for' => 'folder_size'
                    ])
                    ->group('folder_size-flex', 'div', function ($form) {
                        $form->number('folder_size', [
                            'class' => 'form-control',
                            'min'   => 0,
                            'value' => $this->values[ 'folder_size' ]
                        ])->html('folder_size-unit', '<span:attr>:_content</span>', [
                            '_content'     => 'Mo',
                            'data-tooltip' => 'Mega octet'
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group col-sm-12' ]);
            })
            ->group('file-fieldset', 'fieldset', function ($form) {
                $form->legend('file-profil-legend', t('Files permissions'))
                ->group('file_store-group', 'div', function ($form) {
                    $form->checkbox('file_store', [ 'checked' => $this->values[ 'file_store' ] ])
                    ->label('file_store-label', '<i class="ui"></i><i class="fa fa-plus"></i> ' . t('Create'), [
                        'for' => 'file_store' ]);
                }, [ 'class' => 'form-group col-sm-4' ])
                ->group('file_update-group', 'div', function ($form) {
                    $form->checkbox('file_update', [ 'checked' => $this->values[ 'file_update' ] ])
                    ->label('file_update-label', '<i class="ui"></i><i class="fa fa-edit"></i> ' . t('Edit'), [
                        'for' => 'file_update' ]);
                }, [ 'class' => 'form-group col-sm-4' ])
                ->group('file_delete-group', 'div', function ($form) {
                    $form->checkbox('file_delete', [ 'checked' => $this->values[ 'file_delete' ] ])
                    ->label('file_delete-label', '<i class="ui"></i><i class="fa fa-times"></i> ' . t('Delete'), [
                        'for' => 'file_delete' ]);
                }, [ 'class' => 'form-group col-sm-4' ])
                ->group('file_download-group', 'div', function ($form) {
                    $form->checkbox('file_download', [ 'checked' => $this->values[ 'file_download' ] ])
                    ->label('file_download-label', '<i class="ui"></i><i class="fa fa-download"></i> ' . t('Download'), [
                        'for' => 'file_download' ]);
                }, [ 'class' => 'form-group col-sm-4' ])
                ->group('file_clipboard-group', 'div', function ($form) {
                    $form->checkbox('file_clipboard', [ 'checked' => $this->values[ 'file_clipboard' ] ])
                    ->label('file_clipboard-label', '<i class="ui"></i><i class="fa fa-copy"></i> ' . t('Copy link'), [
                        'for' => 'file_clipboard' ]);
                }, [ 'class' => 'form-group col-sm-8' ])
                ->group('file_size-group', 'div', function ($form) {
                    $form->label('file_size-label', t('Size limit by file'), [
                        'for' => 'file_size'
                    ])
                    ->group('file_size-flex', 'div', function ($form) {
                        $form->number('file_size', [
                            'class' => 'form-control',
                            'min'   => 0,
                            'value' => $this->values[ 'file_size' ]
                        ])->html('file_size-unit', '<span:attr>:_content</span>', [
                            '_content'     => 'Mo',
                            'data-tooltip' => 'Mega octet'
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group col-sm-12' ]);
            })
            ->group('extensions-fieldset', 'fieldset', function ($form) {
                $form->legend('extensions-legend', t('File extensions'))
                ->group('file_extensions_all-group', 'div', function ($form) {
                    $form->checkbox('file_extensions_all', [
                        'checked' => $this->values[ 'file_extensions_all' ]
                    ])
                    ->label('all_extensions-label', '<i class="ui"></i>' . t('All extensions'), [
                        'for' => 'file_extensions_all'
                    ]);
                }, [ 'class' => 'form-group col-md-12' ]);
                foreach (FileManager::getWhiteList() as $extension) {
                    $form->group("$extension-group", 'div', function ($form) use ($extension) {
                        $form->checkbox("file_extensions[$extension]", [
                            'class'   => 'ext',
                            'checked' => in_array($extension, $this->values[ 'file_extensions' ]),
                            'value'   => $extension
                        ])
                        ->label("$extension-label", '<i class="ui"></i>' . $extension, [
                            'for' => "file_extensions[$extension]" ]);
                    }, [ 'class' => 'form-group col-sm-3' ]);
                }
            })
            ->token('token_file_permission')
            ->html('cancel', '<button:attr>:_content</button>', [
                '_content' => t('Cancel'),
                'class'    => 'btn btn-danger',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ])
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        return $this;
    }
}
