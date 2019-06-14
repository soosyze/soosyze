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
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routing.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function form()
    {
        $content = [ 'name' => '', 'email' => '', 'object' => '', 'message' => '' ];

        $this->container->callHook('contact.form.data', [ &$content ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('contact.check');
        $form = (new FormContact([ 'method' => 'post', 'action' => $action ]))->generate($content);

        $this->container->callHook('contact.form', [ &$form, $content ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => 'Contact'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-contact.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function formCheck($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'name'    => 'required|string|max:255',
                'email'   => 'required|email',
                'object'  => 'required|string|max:255',
                'message' => 'required|string|max:5000',
                'copy'    => 'bool',
                'token'   => 'required|token'
            ])
            ->setInputs($post);

        $this->container->callHook('contact.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $inputs = $validator->getInputs();

            $this->container->callHook('contact.before', [ &$validator, &$inputs ]);
            $mail   = (new Email())
                ->to(self::config()->get('settings.email'))
                ->from($inputs[ 'email' ], $inputs[ 'name' ])
                ->subject($inputs[ 'object' ])
                ->message($inputs[ 'message' ]);

            if ($validator->getInput('copy')) {
                $mail->addCc($inputs[ 'email' ]);
            }
            $this->container->callHook('contact.after', [ &$validator ]);
            
            if ($mail->send()) {
                $_SESSION[ 'messages' ][ 'success' ] = [ 'Votre message a bien été envoyé.' ];
            } else {
                $_SESSION[ 'messages' ][ 'errors' ] = [ 'Une erreur a empêché votre email d\'être envoyé.' ];
            }
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }

        $route = self::router()->getRoute('contact');

        return new Redirect($route);
    }
}
