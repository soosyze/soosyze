<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Contact\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Validator\Validator;
use Soosyze\Core\Modules\Contact\Form\FormContact;

/**
 * @method \Soosyze\Core\Modules\Mailer\Services\Mailer       mailer()
 * @method \Soosyze\Core\Modules\Template\Services\Templating template()
 */
class Contact extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function form(): ResponseInterface
    {
        $values = [];
        $this->container->callHook('contact.form.data', [ &$values ]);

        $action = self::router()->generateUrl('contact.check');

        $form = (new FormContact([ 'action' => $action, 'method' => 'post' ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('contact.form', [ &$form, $values ]);

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => t('Contact')
                ])
                ->make('page.content', 'contact/content-contact-form.php', $this->pathViews, [
                    'form' => $form
                ])
                ->override('page', [ 'page-contact.php' ]);
    }

    public function formCheck(ServerRequestInterface $req): ResponseInterface
    {
        $validator = (new Validator())
            ->setRules([
                'name'          => 'required|string|max:255',
                'email'         => 'required|email',
                'object'        => 'required|string|max:255',
                'message'       => 'required|string|max:5000',
                'copy'          => 'bool',
                'token_contact' => 'required|token'
            ])
            ->setLabels([
                'name'    => t('Name'),
                'email'   => t('E-mail'),
                'object'  => t('Object'),
                'message' => t('Message'),
                'copy'    => t('Send me a copy of the mail'),
            ])
            ->setInputs((array) $req->getParsedBody());

        $this->container->callHook('contact.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $inputs = $validator->getInputs();
            /** @phpstan-var string $to */
            $to = self::config()->get('mailer.email');

            $this->container->callHook('contact.before', [ &$validator, &$inputs ]);
            $mail = self::mailer()
                ->from($inputs[ 'email' ], $inputs[ 'name' ])
                ->to($to)
                ->subject($inputs[ 'object' ])
                ->message($inputs[ 'message' ]);

            if ($validator->getInput('copy')) {
                $mail->addCc($inputs[ 'email' ]);
            }
            $this->container->callHook('contact.after', [ &$validator ]);

            if ($mail->send()) {
                $_SESSION[ 'messages' ][ 'success' ][] = t('Your message has been sent.');

                return $this->json(200, [ 'redirect' => self::router()->generateUrl('contact.form') ]);
            } else {
                return $this->json(400, [
                        'messages' => [ 'errors' => [ t('An error prevented your email from being sent.') ] ]
                ]);
            }
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }
}
