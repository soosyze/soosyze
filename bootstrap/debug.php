<?php

register_shutdown_function('handlerFatal');
set_error_handler('handlerError');
set_exception_handler('handlerException');
ini_set('display_errors', 0);
error_reporting(E_ALL);

/**
 * Gestionnaire des erreurs.
 *
 * @global type $config Les configurations pour la gestion du débugage.
 *
 * @param int    $num     Niveau d'erreur, sous la forme d'un entier.
 * @param string $str     Message d'erreur, sous forme d'une chaîne de caractères.
 * @param string $file    Nom du fichier d'où provient l'erreur.
 * @param int    $line    Numéro de ligne du fichier d'où provient l'erreur.
 * @param array  $context Contient un tableau avec toutes les variables qui
 *                        existaient lorsque l'erreur a été déclenchée.
 */
function handlerError($num, $str, $file, $line, array $context = [])
{
    global $config;

    if ($config[ 'debug' ]) {
        $msg = parseCode($num) . ' ' . $str;
        printException(new \ErrorException($msg, 0, $num, $file, $line));
    }
}

/**
 * Gestionnaire des exceptions.
 *
 * @global array $config Configurations pour la gestion du débugage.
 *
 * @param Exception|Throwable $exp
 */
function handlerException($exp)
{
    global $config;

    if ($config[ 'debug' ]) {
        
        /* Pour les exceptions PHP <= 5.6 */
        if ($exp instanceof \Exception) {
            header('HTTP/1.0 500 Internal Server Error');
            printException($exp);
            exit();
        }
        /* Pour les exception PHP >= 7.0 */
        if ($exp instanceof \Throwable) {
            printException($exp);
        }
    }
}

/**
 * Gestionnaire des erreurs fatal PHP <= 5.6.
 * Les erreurs fatales deviennent des objets \Throwable en PHP >= 7.0.
 *
 * @global array $config Configurations pour la gestion du débugage.
 */
function handlerFatal()
{
    global $config;

    if ($config[ 'debug' ] && ($error = error_get_last())) {
        extract($error);
        handlerError($type, $message, $file, $line);
    }
}

/**
 * Affichage de l'erreur.
 *
 * @param Exception|Throwable $exp
 */
function printException($exp)
{
    $trace = array_reverse($exp->getTrace());
    $html  = '<style>
            table{width: 100%; border-collapse: collapse; margin-bottom: 3px;}
            thead{background-color: #272822; color:#FFF;}
            thead th{padding: 10px; border-bottom: 3px solid #FFF;}
            th{border:0px;}
            .table-trace, .table-exp {font-family: Roboto,"Source Sans Pro",sans-serif;}
            .table-trace td{border-left: #FFF 3px solid;}
            .table-trace tr:hover td,
            .table-trace tr:hover th{background-color: #272822; color:#FFF;}
            .table-exp{background-color: rgb(190, 50, 50); color: #FFF;}
            .exp-class,
            .exp-function{font-weight: bold;}
            .exp-class{color: #BE7132;}
            .exp-function{color: #1E7272;}
            .table-exp th, 
            .table-exp td,
            .table-trace th, 
            .table-trace td{padding: 10px;}
            .two th,
            .two td{background-color: #E9E9E9;}
            .arg-string{color: #289828;}
            .arg-object{color: #be7132;}
            .arg-numeric,
            .arg-bool,
            .arg-null,
            .arg-resource{color: #d19a66;}
            </style>

            <div style=\'width: 80%; margin-left: auto; margin-right: auto; overflow-x:auto\'>
            <h1 style=\'color: rgb(190, 50, 50); text-align:center;\'>✘ Exception Occured</h1>
            <table class=\'table-exp\'>
               <tr>
                   <th>Type</th>
                   <td>' . get_class($exp) . "</td>
               </tr>
               <tr>
                   <th>File</th>
                   <td>{$exp->getFile()} : {$exp->getLine()}</td>
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
        $class = $key % 2
            ? 'two'
            : 'one';

        $html .= "<tr class='$class'><th>#$key</th><td>";
        $html .= isset($stackPoint[ 'class' ])
            ? "<span class='exp-class'>{$stackPoint[ 'class' ]}-></span>"
            : '';
        $html .= "<span class='exp-function'>{$stackPoint[ 'function' ]}(" . parseArg($stackPoint[ 'args' ]) . ')</span></td>';
        if (!isset($stackPoint[ 'file' ])) {
            $stackPoint[ 'file' ] = isset($trace[ $key - 1 ][ 'file' ])
                ? $trace[ $key - 1 ][ 'file' ]
                : '';
            $stackPoint[ 'line' ] = isset($trace[ $key - 1 ][ 'line' ])
                ? $trace[ $key - 1 ][ 'line' ]
                : '';
        }
        $html .= "<td>{$stackPoint[ 'file' ]}</td><td>{$stackPoint[ 'line' ]}</td></tr>";
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
function parseArg($args)
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
        } elseif (is_object($arg) || $arg instanceof \__PHP_Incomplete_Class) {
            /*
             * __PHP_Incomplete_Class lorsque vous utilisez des objets en session
             * si vous déclarez vos classes avant session_start().
             */
            $html .= '<span class="arg-object">' . get_class($arg) . '</span>, ';
        } elseif (is_numeric($arg)) {
            $html .= '<span class="arg-numeric">' . $arg . '</span>, ';
        } elseif (is_bool($arg)) {
            $html .= '<span class="arg-bool">' . ($arg ? 'true' : 'false') . '</span>, ';
        } elseif (is_null($arg)) {
            $html .= '<span class="arg-null">null</span>, ';
        } elseif (is_resource($arg)) {
            $html .= '<span class="arg-resource">' . get_resource_type($arg) . '</span>, ';
        }
    }

    return substr($html, 0, -2);
}

/**
 * @param int $code
 *
 * @return string
 */
function parseCode($code)
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

    return isset($type[ $code ])
        ? $type[ $code ]
        : '';
}
