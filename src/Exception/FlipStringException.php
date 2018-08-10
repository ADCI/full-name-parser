<?php

/**
 * @file
 * Class of NameParsingException.
 */

namespace ADCI\FullNameParser\Exception;

use \Throwable;

/**
 * Any exception for name parsing.
 *
 * @package FullNameParser
 */
class FlipStringException extends NameParsingException
{
    /**
     * Default message text.
     *
     * @var string
     */
    const MESSAGE = "Can't flip around multiple '%s' characters in name string '%s'.";

    /**
     * {@inheritdoc}
     */
    public function __construct($char = null, $full_name = null, $message = null, $code = 0, Throwable $previous = null)
    {
        $message = sprintf(self::MESSAGE, $char, $full_name);
        parent::__construct($message, $code, $previous);
    }
}
