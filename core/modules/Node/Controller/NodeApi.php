<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Node\Form\FormNodeDelete;
use SoosyzeCore\Template\Services\Block;

class NodeApi extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function remove(int $idNode, ServerRequestInterface $req): Block
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }

        $values[ 'current_path' ] = self::alias()->getAlias('node/' . $node[ 'id' ], 'node/' . $node[ 'id' ]);

        $pathsSettings = self::node()->getPathSettings();

        $useInPath = null;
        foreach ($pathsSettings as $value) {
            if (!empty($value[ 'path' ]) && self::alias()->getSource($value[ 'path' ], $value[ 'path' ]) === 'node/' . $idNode) {
                $useInPath = $value;

                break;
            }
        }

        $this->container->callHook('node.remove.form.data', [ &$node, $idNode ]);

        $action = self::router()->getRoute('node.delete', [ ':id_node' => $idNode ]);

        $form = (new FormNodeDelete([ 'action' => $action, 'method' => 'delete' ], self::router()))
            ->setValues($values, $useInPath)
            ->makeFields();

        $this->container->callHook('node.remove.form', [ &$form, $node, $idNode ]);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('node/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'  => $form,
                    'title' => t('Delete :name content', [ ':name' => $node[ 'title' ] ])
        ]);
    }
}
