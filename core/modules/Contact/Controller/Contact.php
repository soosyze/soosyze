<?php

namespace SoosyzeCore\Contact\Controller;

use Soosyze\Components\Email\Email;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Contact\Form\FormContact;

class Contact extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function form()
    {
        $values = [];

        $this->container->callHook('contact.form.data', [ &$values ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormContact([
            'method' => 'post',
            'action' => self::router()->getRoute('contact.check')
            ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('contact.form', [ &$form, $values ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'class' => 'is-invalid' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => 'Contact'
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-contact.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function formCheck($req)
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
            ->setLabel([
                'name'    => t('Name'),
                'email'   => t('E-mail'),
                'object'  => t('Object'),
                'message' => t('Message'),
                'copy'    => t('Send me a copy of the mail'),
            ])
            ->setInputs($req->getParsedBody());

        $this->container->callHook('contact.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $inputs = $validator->getInputs();

            $this->container->callHook('contact.before', [ &$validator, &$inputs ]);
            $mail = (new Email())
                ->from($inputs[ 'email' ], $inputs[ 'name' ])
                ->to(self::config()->get('settings.email'))
                ->subject($inputs[ 'object' ])
                ->message($inputs[ 'message' ]);

            if ($validator->getInput('copy')) {
                $mail->addCc($inputs[ 'email' ]);
            }
            $this->container->callHook('contact.after', [ &$validator ]);

            if ($mail->send()) {
                $_SESSION[ 'messages' ][ 'success' ] = [ t('Your message has been sent.') ];
            } else {
                $_SESSION[ 'messages' ][ 'errors' ] = [ t('An error prevented your email from being sent.') ];
            }
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }

        $route = self::router()->getRoute('contact');

        return new Redirect($route);
    }
}
