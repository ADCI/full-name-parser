<?php

/**
 * @file
 * Class of IncorrectInputException.
 */

namespace ADCI\FullNameParser\Exception;

/**
 * Exception of incorrect input.
 *
 * @package FullNameParser
 */
class IncorrectInputException extends NameParsingException
{

    /**
     * Default message text.
     *
     * @var string
     */
    const MESSAGE = "Incorrect input to parse.";

    /**
     * {@inheritdoc}
     */
    public function __construct($message = null, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ? $message : self::MESSAGE, $code, $previous);
    }
}
