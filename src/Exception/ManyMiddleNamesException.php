<?php

/**
 * @file
 * Class of ManyMiddleNamesException.
 */

namespace ADCI\FullNameParser\Exception;

/**
 * Exception of many middle names found.
 *
 * Class ManyMiddleNamesException.
 *
 * @package FullNameParser
 */
class ManyMiddleNamesException extends NameParsingException
{
    /**
     * Default message text.
     *
     * @var string
     */
    const MESSAGE = 'Warning: %s middle names';

    /**
     * {@inheritdoc}
     */
    public function __construct($count = null, $message = null, $code = 0, \Throwable $previous = null)
    {
        $message = sprintf(self::MESSAGE, $count);
        parent::__construct($message, $code, $previous);
    }
}
