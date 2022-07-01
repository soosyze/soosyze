<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Contact\Hook;

use Soosyze\Components\Router\Router;
use Soosyze\Core\Modules\Contact\Form\FormContact;
use Soosyze\Core\Modules\Template\Services\Block as ServiceBlock;
use Soosyze\Core\Modules\User\Services\User;

class Block implements \Soosyze\Core\Modules\Block\BlockInterface
{
    /**
     * @var string
     */
    private const PATH_VIEWS = __DIR__ . '/../Views/';

    /**
     * @var Router
     */
    private $router;

    /**
     * @var User
     */
    private $user;

    public function __construct(Router $router, User $user)
    {
        $this->router = $router;
        $this->user   = $user;
    }

    public function hookBlockCreateFormData(array &$blocks): void
    {
        $blocks[ 'contact.form' ] = [
            'description' => t('Displays a contact form.'),
            'hook'        => 'contact',
            'icon'        => 'fas fa-envelope',
            'no_content'  => t('The "Use general contact form" permission must be enabled to use the contact form.'),
            'path'        => self::PATH_VIEWS,
            'title'       => t('Contact form'),
            'tpl'         => 'components/block/contact-form.php'
        ];
    }

    public function hookContact(ServiceBlock $tpl, ?array $options): ?ServiceBlock
    {
        if (!$this->user->isGranted('contact.main')) {
            return null;
        }

        $action = $this->router->generateUrl('contact.check');

        $form = (new FormContact([ 'action' => $action, 'method' => 'post' ]))
            ->makeFields();

        return $tpl->addVar('form', $form);
    }
}
