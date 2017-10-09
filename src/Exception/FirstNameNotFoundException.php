<?php

/**
 * @file
 * Class of FirstNameNotFoundException.
 */

namespace ADCI\FullNameParser\Exception;

/**
 * Exception of first name not found.
 *
 * Class FirstNameNotFoundException.
 *
 * @package FullNameParser
 */
class FirstNameNotFoundException extends NameParsingException
{
    /**
     * Default message text.
     *
     * @var string
     */
    const MESSAGE = "Couldn't find a first name.";

    /**
     * {@inheritdoc}
     */
    public function __construct($message = null, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ? $message : self::MESSAGE, $code, $previous);
    }
}
