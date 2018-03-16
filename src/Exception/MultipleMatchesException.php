<?php

/**
 * @file
 * Class of IncorrectInputException.
 */

namespace ADCI\FullNameParser\Exception;

/**
 * Exception of incorrect input.
 *
 * Class IncorrectInputException.
 *
 * @package FullNameParser
 */
class MultipleMatchesException extends NameParsingException
{

    /**
     * Default message text.
     *
     * @var string
     */
    const MESSAGE = "The regex being used has multiple matches.";

    /**
     * {@inheritdoc}
     */
    public function __construct($message = null, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ? $message : self::MESSAGE, $code, $previous);
    }
}
