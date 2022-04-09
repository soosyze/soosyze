<?php

declare(strict_types=1);

namespace SoosyzeCore\FileManager\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\FileManager\Hook\Config;
use SoosyzeCore\FileManager\Hook\User;
use SoosyzeCore\FileManager\Services\FilterManagerIterator;
use SoosyzeCore\Template\Services\Block;

/**
 * @method \SoosyzeCore\FileManager\Services\FileManager filemanager()
 * @method \SoosyzeCore\Template\Services\Templating     template()
 */
class FileCopy extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    /**
     * @return Block|ResponseInterface
     */
    public function admin(string $path, string $name, string $ext, ServerRequestInterface $req)
    {
        $path = Util::cleanPath('/' . $path);
        $spl  = new \SplFileInfo(
            self::core()->getDir('files_public', 'app/files') . "$path$name$ext"
        );

        if (!$spl->isFile()) {
            return $this->get404($req);
        }

        $action = self::router()->generateUrl('filemanager.copy.update', [
            ':path' => $path, ':name' => $name, ':ext'  => $ext
        ]);

        $form = (new FormBuilder([ 'action' => $action, 'method' => 'post' ]))
            ->token('token_file_copy');

        $content = self::template()
            ->getTheme('theme_admin')
            ->createBlock('filemanager/content-file_manager-admin_copy.php', $this->pathViews)
            ->addVar('filemanager', $this->getFileManager($path, $req));

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-form_copy.php', $this->pathViews)
                ->addVars([
                    'content' => $content,
                    'form'    => $form,
                    'info'    => self::filemanager()->parseFile($spl, $path),
                    'menu'    => self::filemanager()->getFileSubmenu('filemanager.file.copy', $spl, $path),
                    'title'   => t('Select the target folder')
        ]);
    }

    public function show(string $path, ServerRequestInterface $req): Block
    {
        return $this->getFileManager($path, $req);
    }

    public function filter(string $path, ServerRequestInterface $req): Block
    {
        $path = Util::cleanPath('/' . $path);

        $filesPublic = self::core()->getDir('files_public', 'app/files') . $path;

        $files = [];
        $size  = 0;

        if (is_dir($filesPublic)) {
            $dirIterator = new \DirectoryIterator($filesPublic);

            /** @var FilterManagerIterator $iterator */
            $iterator = $this->get(FilterManagerIterator::class);
            $iterator = $iterator->load($path, $dirIterator);

            /** @phpstan-var \DirectoryIterator $file */
            foreach ($iterator as $file) {
                try {
                    if ($file->isDir()) {
                        $spl = self::filemanager()->parseDir(
                            $file,
                            "$path/",
                            'filemanager.copy.show'
                        );

                        $files[] = $spl;
                        $size    += $spl[ 'size_octet' ];
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            usort($files, static function (array $a, array $b): int {
                if ($a[ 'ext' ] === $b[ 'ext' ]) {
                    return 0;
                }

                return ($a[ 'ext' ] === 'dir')
                    ? -1
                    : 1;
            });
        }

        /** @var User $hookUser */
        $hookUser =  $this->get(User::class);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/table-files_copy.php', $this->pathViews)
                ->addVars([
                    'files'        => $files,
                    'link_show'    => self::router()->generateUrl('filemanager.copy.show', [
                        ':path' => $path
                    ]),
                    'path'         => $path,
                    'profil'       => $hookUser->getRight($path),
                    'size_all'     => Util::strFileSizeFormatted($size),
                    'text_copy'    => $path === ''
                    ? t('Copy')
                    : t('Copy to :dir', [ ':dir' => $path ]),
                    'text_deplace' => $path === ''
                    ? t('Deplace')
                    : t('Deplace to :dir', [ ':dir' => $path ])
        ]);
    }

    public function update(string $path, string $name, string $ext, ServerRequestInterface $req): ResponseInterface
    {
        $path = Util::cleanPath('/' . $path);

        $fileCurrent = self::core()->getDir('files_public', 'app/files') . "$path$name$ext";

        $validator = (new Validator())
            ->setRules([
                'copy'            => '!required_with:deplace',
                'deplace'         => '!required_with:copy',
                'dir'             => 'required|dir|regex:#^(/[-\w]+){0,255}#',
                'file_current'    => 'required|is_file',
                'token_file_copy' => 'token'
            ])
            ->addLabels([
                'copy'    => t('Copy'),
                'deplace' => t('Deplace'),
                'dir'     => t('Directory'),
                'file'    => t('File')
            ])
            ->setInputs(array_replace([ 'copy' => '', 'deplace' => '' ], (array) $req->getParsedBody()))
            ->addInput('file_current', $fileCurrent);

        $dirTarget = self::core()->getDir('files_public', 'app/files') . $validator->getInput('dir');
        $validator->addInput('dir', $dirTarget);

        /* Si les valeur attendues sont les bonnes. */
        if (!$validator->isValid()) {
            return $this->json(400, [
                    'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                    'errors_keys' => $validator->getKeyInputErrors()
            ]);
        }

        $fileTarget = "$dirTarget$name$ext";
        if (self::config()->get('settings.replace_file') === Config::KEEP_RENAME) {
            $fileTarget = $this->isResolveName($dirTarget, $name, $ext);
        } elseif (self::config()->get('settings.replace_file') === Config::KEEP_REFUSE && is_file($fileTarget)) {
            return $this->json(400, [
                    'messages' => [ 'errors' => [ t('An existing file has the same name, you can not replace it') ] ]
            ]);
        }

        copy($fileCurrent, $fileTarget);
        $messageSuccess = t('The directory is copied');
        if ($validator->getInput('deplace')) {
            unlink($fileCurrent);
            $messageSuccess = t('The directory is moved');
        }

        return $this->json(200, [
                'messages' => [ 'success' => [ $messageSuccess ] ]
        ]);
    }

    private function getFileManager(string $path, ServerRequestInterface $req): Block
    {
        $breadcrumb = self::template()
            ->getTheme('theme_admin')
            ->createBlock('filemanager/breadcrumb-file_manager-show.php', $this->pathViews)
            ->addVars([
            'granted_folder_create' => false,
            'links'                 => self::filemanager()->getBreadcrumb($path, 'filemanager.copy.show')
        ]);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/content-file_manager-show_copy.php', $this->pathViews)
                ->addBlock('breadcrumb', $breadcrumb)
                ->addBlock('table', $this->filter($path, $req));
    }

    private function isResolveName(string $dir, string $name, string $ext): string
    {
        $file = "$dir$name$ext";

        if (is_file($file)) {
            $i = 1;
            while (is_file("{$dir}{$name}_{$i}{$ext}")) {
                ++$i;
            }

            return "{$dir}{$name}_{$i}{$ext}";
        }

        return $file;
    }
}
