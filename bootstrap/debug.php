<?php

register_shutdown_function('handlerFatal');
set_error_handler('handlerError');
set_exception_handler('handlerException');
ini_set('display_errors', '0');
error_reporting(E_ALL);

/**
 * Gestionnaire des erreurs.
 *
 * @global type $config Les configurations pour la gestion du débugage.
 *
 * @param int    $errno      Niveau d'erreur, sous la forme d'un entier.
 * @param string $errstr     Message d'erreur, sous forme d'une chaîne de caractères.
 * @param string $errfile    Nom du fichier d'où provient l'erreur.
 * @param int    $errline    Numéro de ligne du fichier d'où provient l'erreur.
 * @param array  $errcontext Contient un tableau avec toutes les variables qui
 *                           existaient lorsque l'erreur a été déclenchée.
 */
function handlerError(
    int $errno,
    string $errstr,
    string $errfile,
    int $errline,
    array $errcontext = []
): bool {
    global $config;

    if ($config[ 'debug' ]) {
        $msg = parseCode($errno) . ' ' . $errstr;
        printException(new \ErrorException($msg, 0, $errno, $errfile, $errline));

        return true;
    }

    return false;
}

/**
 * Gestionnaire des exceptions.
 *
 * @global array $config Configurations pour la gestion du débugage.
 *
 * @param Exception|Throwable $exp
 */
function handlerException($exp): void
{
    global $config;

    if ($config[ 'debug' ]) {
        /* Pour les exception PHP >= 7.0 */
        if ($exp instanceof \Throwable) {
            header('HTTP/1.0 500 Internal Server Error');
            printException($exp);
            exit();
        }
    }
}

/**
 * Gestionnaire des erreurs fatal PHP <= 5.6.
 * Les erreurs fatales deviennent des objets \Throwable en PHP >= 7.0.
 *
 * @global array $config Configurations pour la gestion du débugage.
 */
function handlerFatal(): void
{
    global $config;

    if ($config[ 'debug' ] && ($error = error_get_last())) {
        handlerError($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

/**
 * Affichage de l'erreur.
 *
 * @param Exception|Throwable $exp
 */
function printException($exp): void
{
    $trace = array_reverse($exp->getTrace());
    $html  = '<style>
            .table-trace, .table-exp {width: 100%; border-collapse: collapse; margin-bottom: 3px; font-family: Roboto,"Source Sans Pro",sans-serif;}
            .table-trace{ background-color: #FFF; }
            .table-trace thead{background-color: #272822; color:#FFF;}
            .table-trace thead th{padding: 10px; border-bottom: 3px solid #FFF;}
            .table-trace th{border:0px;}
            .table-trace td{border-left: #FFF 3px solid;}
            .table-trace tr:hover th, .table-exp tr:hover th,
            .table-trace tr:hover td, .table-exp tr:hover td{background-color: #272822; color:#FFF;}
            .table-exp{background-color: rgb(190, 50, 50); color: #FFF;}
            .table-exp th, .table-trace th,
            .table-exp td, .table-trace td{padding: 10px;}
            .two th, .two td{background-color: #E9E9E9;}
            .arg-string{color: #289828;}
            .arg-object{color: #be7132;}
            .arg-numeric,
            .arg-bool,
            .arg-null,
            .arg-resource{color: #d19a66;}
            .exp-class,
            .exp-function{font-weight: bold;}
            .exp-class{color: #BE7132;}
            .exp-function{color: #1E7272;}
            </style>

            <div style=\'width: 80%; margin-left: auto; margin-right: auto; overflow-x:auto\'>
            <h1 style=\'color: rgb(190, 50, 50); text-align:center;\'>✘ Exception Occured</h1>
            <table class=\'table-exp\'>
               <tr>
                   <th>Type</th>
                   <td>' . get_class($exp) . '</td>
               </tr>
               <tr>
                   <th>File</th>
                   <td>' . str_replace(ROOT, '', $exp->getFile()) . " : {$exp->getLine()}</td>
               </tr>
               <tr>
                   <th>Message</th>
                   <td>{$exp->getMessage()}</td>
               </tr>
            </table>
            <table class='table-trace'>
               <thead>
                   <tr>
                       <th>N°</th>
                       <th>Function</th>
                       <th>Location</th>
                       <th>Lines</th>
                   </tr>
               </thead>
               <tbody>";
    foreach ($trace as $key => $stackPoint) {
        $classCss = $key % 2
            ? 'two'
            : 'one';
        $className = isset($stackPoint[ 'class' ], $stackPoint[ 'type' ])
            ? "<span class='exp-class'>{$stackPoint[ 'class' ]}{$stackPoint[ 'type' ]}</span>"
            : '';
        $closure  = strstr($stackPoint[ 'function' ], '{');
        $stackPoint[ 'function' ] = $closure
            ? $closure
            : $stackPoint[ 'function' ];
        $args = isset($stackPoint[ 'args' ])
            ? parseArg($stackPoint[ 'args' ])
            : '';

        $file = $stackPoint[ 'file' ] ?? $trace[ $key - 1 ][ 'file' ] ?? '';
        $line = $stackPoint[ 'line' ] ?? $trace[ $key - 1 ][ 'line' ] ?? '';

        $html .= sprintf(
            '<tr class=\'%s\'><th>#%s</th> <td>%s%s</td> <td>%s</td> <td>%s</td></tr>',
            $classCss,
            $key,
            $className,
            "<span class='exp-function'>{$stackPoint[ 'function' ]}({$args})</span>",
            str_replace(ROOT, '', $file),
            $line
        );
    }
    $html .= '</tbody>
            </table>
            </div>';
    echo $html;
}

/**
 * Met en forme un ensemble de données en fonction de leur type.
 *
 * @param array|mixed $args
 *
 * @return string Mise en forme HTML.
 */
function parseArg($args): string
{
    $html = '';
    if (!is_array($args)) {
        $args = [$args];
    }
    foreach ($args as $arg) {
        if (is_string($arg)) {
            $html .= '<span class="arg-string">"' . htmlspecialchars($arg) . '"</span>, ';
        } elseif (is_array($arg)) {
            $html .= '<span class="arg-array">[ ' . parseArg($arg) . ' ]</span>, ';
        } elseif (is_object($arg)) {
            $html .= '<span class="arg-object">' . get_class($arg) . '</span>, ';
        } elseif (is_numeric($arg)) {
            $html .= '<span class="arg-numeric">' . $arg . '</span>, ';
        } elseif (is_bool($arg)) {
            $html .= '<span class="arg-bool">' . ($arg ? 'true' : 'false') . '</span>, ';
        } elseif ($arg === null) {
            $html .= '<span class="arg-null">null</span>, ';
        } elseif (is_resource($arg)) {
            $html .= '<span class="arg-resource">' . get_resource_type($arg) . '</span>, ';
        }
    }

    return substr($html, 0, -2);
}

function parseCode(int $code): string
{
    $type = [
        E_ERROR             => 'E_ERROR',
        E_WARNING           => 'E_WARNING',
        E_PARSE             => 'E_PARSE',
        E_NOTICE            => 'E_NOTICE',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_STRICT            => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED        => 'E_DEPRECATED',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
    ];

    return $type[ $code ] ?? '';
}
