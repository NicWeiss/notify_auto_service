<?php

namespace lib\migration;

/**
 * PHP error handler
 * @param int $type
 * @param string $message
 * @param string $file
 * @param int $line
 */
function std_mig_error_handler($type, $message, $file, $line)
{
    global $file_to_delete;
    //skip all errors during @function calls
    if (error_reporting() == 0)
        return;

    $context = array(
        'type' => 'untyped',
        'file' => $file,
        'line' => $line
    );

    if ($file_to_delete)
        unlink($file_to_delete);
}

/**
 * PHP exception handler
 * @param exception $ex
 * @return string
 */
function std_mig_exception_handler($ex)
{
    global $file_to_delete;
    $context = array(
        'type' => $ex->getCode(),
        'file' => $ex->getFile(),
        'line' => $ex->getLine()
    );

    if ($file_to_delete)
        unlink($file_to_delete);
}

/**
 * PHP fatal error handler
 */
function std_mig_fatal_handler()
{
    global $file_to_delete;
    $context = error_get_last();
    if (!$context)
        return;

    //skip non-fatal errors
    $fatal = array(E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR);
    if (array_search($context['type'], $fatal) === false)
        return;
    $context['type'] = 'untyped';
    $message = $context['message'];
    unset($context['message']);

    if ($file_to_delete)
        unlink($file_to_delete);
}
