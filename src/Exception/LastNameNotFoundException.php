<?php

/**
 * @file
 * Class of LastNameNotFoundException.
 */

namespace ADCI\FullNameParser\Exception;

/**
 * Exception last name not found.
 *
 * Class LastNameNotFoundException.
 *
 * @package FullNameParser
 */
class LastNameNotFoundException extends NameParsingException
{

    /**
     * Default message text.
     *
     * @var string
     */
    const MESSAGE = "Couldn't find a last name.";

    /**
     * {@inheritdoc}
     */
    public function __construct($message = null, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ? $message : self::MESSAGE, $code, $previous);
    }
}
