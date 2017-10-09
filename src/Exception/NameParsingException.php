<?php

/**
 * @file
 * Class of NameParsingException.
 */

namespace ADCI\FullNameParser\Exception;

/**
 * Any exception for name parsing.
 *
 * Class NameParsingException.
 *
 * @package FullNameParser
 */
class NameParsingException extends \Exception
{
    /**
     * NameParsingException constructor.
     *
     * @param string $message
     * Message of error.
     * @param int $code
     * Code of error.
     * @param \Throwable|null $previous
     * Previously chained error.
     */
    public function __construct($message, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
