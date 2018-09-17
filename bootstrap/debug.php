<?php

/**
 * Error handler, passes flow over the exception logger with new ErrorException.
 */
function errorHandler($num, $str, $file, $line, $context = null)
{
    global $config;

    if ($config[ "debug" ] == true) {
        printException(new \ErrorException($str, 0, $num, $file, $line));
    }
}

/**
 * Uncaught exception handler.
 */
function exceptionHandler(Exception $exp)
{
    global $config;
    header("HTTP/1.0 500 Internal Server Error");
    if ($config[ "debug" ] == true) {
        printException($exp);
    }

    exit();
}

/**
 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
 */
function fatalHandler()
{
    $error = error_get_last();
    if ($error[ "type" ] == E_ERROR) {
        errorHandler($error[ "type" ], $error[ "message" ], $error[ "file" ], $error[ "line" ]);
    }
}

function printException(Exception $exp)
{
    $trace = array_reverse($exp->getTrace());
    $html  = "<style>
            table{width: 100%; border-collapse: collapse; margin-bottom: 3px;}
            thead{background-color: #272822; color:#FFF;}
            thead th{padding: 10px; border-bottom: 3px solid #FFF;}
            th{border:0px;}
            .table-trace td{border-left: #FFF 3px solid;}
            .table-trace .row:hover td,
            .table-trace .row:hover th{background-color: #272822; color:#FFF;}
            .table-exp{background-color: rgb(190, 50, 50); color: #FFF;}
            .exp-class,
            .exp-function{font-weight: bold;}
            .exp-class{color: #BE7132;}
            .exp-function{color: #1E7272;}
            .exp-args{color: #289828;}
            .row th,
            .row td{padding: 10px;}
            .two th,
            .two td{background-color:#E9E9E9}
            </style>

         <div style='width: 80%; margin-left: auto; margin-right: auto; overflow-x:auto'>
         <h1 style='color: rgb(190, 50, 50); text-align:center;'>✘ Exception Occured</h1>
         <table class='table-exp'>
            <tr class='row'>
                <th>Type</th>
                <td>" . get_class($exp) . "</td>
            </tr>
            <tr class='row'>
                <th>File</th>
                <td>{$exp->getFile()} : {$exp->getLine()}</td>
            </tr>
            <tr class='row'>
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
            ? "two"
            : "one";

        $html .= "<tr class='row {$class}'><th>#{$key}</th><td>";
        $html .= isset($stackPoint[ 'class' ])
            ? "<span class='exp-class'>{$stackPoint[ 'class' ]}-></span>"
            : "";

        $html .= "<span class='exp-function'>{$stackPoint[ 'function' ]}( <span class='exp-args'>" . parseArg($stackPoint[ 'args' ]) . " </span>)</span></td>";
        if (isset($stackPoint[ 'file' ])) {
            $html .= "<td>{$stackPoint[ 'file' ]}</td><td>{$stackPoint[ 'line' ]}</td>";
        } else {
            $file = isset($trace[ $key - 1 ][ 'file' ])
                ? $trace[ $key - 1 ][ 'file' ]
                : '';
            $line = isset($trace[ $key - 1 ][ 'line' ])
                ? $trace[ $key - 1 ][ 'line' ]
                : '';
            $html .= "<td>{$file}</td>" . "<td>{$line}</td>";
        }
        $html .= "</tr>";
    }
    $html .= "</tbody>
            </table>
            </div>";
    print $html;
}

function parseArg(array $args)
{
    $html = "";
    foreach ($args as $arg) {
        if (is_string($arg)) {
            $html .= '"' . htmlspecialchars($arg) . '", ';
        } elseif (is_array($arg)) {
            $html .= '[ ' . parseArg($arg) . ' ], ';
        } elseif (is_object($arg)) {
            $html .= get_class($arg) . ', ';
        } elseif (is_numeric($arg)) {
            $html .= $arg . ', ';
        }
    }

    return substr($html, 0, -2);
}

register_shutdown_function("fatalHandler");
set_error_handler("errorHandler");
set_exception_handler("exceptionHandler");
ini_set("display_errors", 0);
error_reporting(E_ALL);
