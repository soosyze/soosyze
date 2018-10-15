<?php

namespace Contact\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Email\Email;

define("CONFIG_CONTACT", MODULES_CORE . 'Contact' . DS . 'Config' . DS);

class Contact extends \Soosyze\Controller
{
    protected $pathRoutes = CONFIG_CONTACT . 'routing.json';

    protected $pathServices = CONFIG_CONTACT . 'service.json';

    public function contact()
    {
        $content = [ 'name' => '', 'email' => '', 'object' => '', 'message' => '' ];

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('contact.check');

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('contact-name-group', 'div', function ($form) use ($content) {
                $form->label('contact-name-label', 'Votre nom')
                ->text('name', 'name', [
                    'class'    => 'form-control',
                    'required' => 1,
                    'value'    => $content[ 'name' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('contact-email-group', 'div', function ($form) use ($content) {
                $form->label('contact-email-label', 'Votre adresse de courriel')
                ->email('email', 'email', [
                    'class'    => 'form-control',
                    'required' => 1,
                    'value'    => $content[ 'email' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('contact-object-group', 'div', function ($form) use ($content) {
                $form->label('contact-object-label', 'Objet')
                ->text('object', 'object', [
                    'class'    => 'form-control',
                    'required' => 1,
                    'value'    => $content[ 'object' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('contact-message-group', 'div', function ($form) use ($content) {
                $form->label('contact-message-label', 'Message')
                ->textarea('message', 'message', $content[ 'message' ], [
                    'class'    => 'form-control',
                    'required' => 1,
                    'rows'     => 8,
                    'style'    => 'resize:vertical'
                ]);
            }, [ 'class' => 'form-group' ])
            ->token()
            ->submit('submit', 'Envoyer le message', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        return self::template()
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Contact'
                ])
                ->render('page.content', 'page-contact.php', MODULES_CORE . 'Contact' . DS . 'Views' . DS, [
                    'form' => $form
        ]);
    }

    public function contactCheck($r)
    {
        $post = $r->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'name'    => 'required|string|max:255',
                'email'   => 'required|email',
                'object'  => 'required|string|max:255',
                'message' => 'required|string',
                'token'   => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $inputs = $validator->getInputs();
            $mail   = new Email;
            $isSend = $mail->to(self::config()->get('settings.email'))
                ->from($inputs[ 'email' ], $inputs[ 'name' ])
                ->subject($inputs[ 'object' ])
                ->message($inputs[ 'message' ])
                ->send();

            if ($mail->send()) {
                $_SESSION[ 'success' ] = [ 'Votre message a bien été envoyé.' ];
            } else {
                $_SESSION[ 'errors' ] = [ 'Une erreur a empêché votre mail d\'être envoyé.' ];
            }
        } else {
            $_SESSION[ 'inputs' ]      = $validator->getInputs();
            $_SESSION[ 'errors' ]      = $validator->getErrors();
            $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();
        }

        $route = self::router()->getRoute('contact');

        return new Redirect($route);
    }
}
