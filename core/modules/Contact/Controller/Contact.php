<?php

namespace Contact\Controller;

use Soosyze\Components\Email\Email;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

define('CONFIG_CONTACT', MODULES_CORE . 'Contact' . DS . 'Config' . DS);

class Contact extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = CONFIG_CONTACT . 'service.json';
        $this->pathRoutes   = CONFIG_CONTACT . 'routing.json';
    }

    public function contact()
    {
        $content = [ 'name' => '', 'email' => '', 'object' => '', 'message' => '' ];
        
        $this->container->callHook('contact.form.data', [ &$content ]);

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
            ->group('contact-copy-group', 'div', function ($form) {
                $form->checkbox('copy', 'copy')
                ->label('contact-copy-label', 'M\'envoyer une copie du mail', [
                    'for' => 'copy'
                ]);
            }, [ 'class' => 'form-group' ])
            ->token()
            ->submit('submit', 'Envoyer le message', [ 'class' => 'btn btn-success' ]);
        
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
                ->render('page.content', 'page-contact.php', MODULES_CORE . 'Contact' . DS . 'Views' . DS, [
                    'form' => $form
        ]);
    }

    public function contactCheck($req)
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
            $mail   = (new Email())
                ->to(self::config()->get('settings.email'))
                ->from($inputs[ 'email' ], $inputs[ 'name' ])
                ->subject($inputs[ 'object' ])
                ->message($inputs[ 'message' ]);

            if ($validator->getInput('copy')) {
                $mail->addCc($inputs[ 'email' ]);
            }
            
            $this->container->callHook('contact.before', [ &$validator, &$inputs ]);
            if ($mail->send()) {
                $_SESSION[ 'messages' ][ 'success' ] = [ 'Votre message a bien été envoyé.' ];
            } else {
                $_SESSION[ 'messages' ][ 'errors' ] = [ 'Une erreur a empêché votre email d\'être envoyé.' ];
            }
            $this->container->callHook('contact.after', [ &$validator ]);
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }

        $route = self::router()->getRoute('contact');

        return new Redirect($route);
    }
}
