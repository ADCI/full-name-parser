<?php

/**
 * @file
 * Class of Name object.
 */

namespace ADCI\FullNameParser;

use ADCI\FullNameParser\Exception\NameParsingException;

/**
 * Class Name.
 *
 * @package FullNameParser
 */
class Name
{

    /**
     * Full name.
     *
     * @var string
     */
    private $fullName;

    /**
     * Leading initial part.
     *
     * @var string
     */
    private $leadingInitial;

    /**
     * First name part.
     *
     * @var string
     */
    private $firstName;

    /**
     * Nicknames part.
     *
     * @var string
     */
    private $nicknames;

    /**
     * Middle name part.
     *
     * @var string
     */
    private $middleName;

    /**
     * Last name part.
     *
     * @var string
     */
    private $lastName;

    /**
     * Title part.
     *
     * @var string
     */
    private $academicTitle;

    /**
     * Suffixes part.
     *
     * @var string
     */
    private $suffix;

    /**
     * Array of parsing error messages.
     *
     * @var array
     */
    private $errors = [];

    /**
     * Parsing result getter.
     *
     * @param string $part
     * Name of part of name to return for.
     *
     * @return self|array|string
     * Return self if all parts needed.
     * Or array if errors needed.
     * Or string of part of name.
     */
    public function getPart($part)
    {
        switch ($part) {
            case 'title':
                return $this->getAcademicTitle();
            case 'first':
                return $this->getFirstName();
            case 'middle':
                return $this->getMiddleName();
            case 'last':
                return $this->getLastName();
            case 'nick':
                return $this->getNicknames();
            case 'suffix':
                return $this->getSuffix();
            case 'error':
                return $this->getErrors();
            case 'all':
            default:
                return $this;
        }
    }

    /**
     * Array of errors getter.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add error message to the array of errors.
     *
     * @param NameParsingException $ex
     * Error to add.
     *
     * @return self
     */
    public function addError(NameParsingException $ex)
    {
        $this->errors[] = $ex->getMessage();
        return $this;
    }

    /**
     * First name getter.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * First name setter.
     *
     * @param string $firstName
     * The first name.
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Nicknames getter.
     *
     * @return string
     */
    public function getNicknames()
    {
        return $this->nicknames;
    }

    /**
     * Nicknames setter.
     *
     * @param string $nicknames
     * The nicknames.
     *
     * @return self
     */
    public function setNicknames($nicknames)
    {
        $this->nicknames = $nicknames;

        return $this;
    }

    /**
     * Middle name getter.
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Middle name setter.
     *
     * @param string $middleName
     * The middle name.
     *
     * @return self
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * Last name getter.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Last name setter.
     *
     * @param string $lastName
     * The last name.
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Suffixes getter.
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Suffixes setter.
     *
     * @param string $suffix
     * The suffix.
     *
     * @return self
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Leading initial getter.
     *
     * @return string
     */
    public function getLeadingInitial()
    {
        return $this->leadingInitial;
    }

    /**
     * Leading initial setter.
     *
     * @param string $leadingInitial
     * The leading initial.
     *
     * @return self
     */
    public function setLeadingInitial($leadingInitial)
    {
        $this->leadingInitial = $leadingInitial;

        return $this;
    }

    /**
     * Academic title getter.
     *
     * @return string
     */
    public function getAcademicTitle()
    {
        return $this->academicTitle;
    }

    /**
     * Title setter.
     *
     * @param string $academicTitle
     * The academic title.
     *
     * @return self
     */
    public function setAcademicTitle($academicTitle)
    {
        $this->academicTitle = $academicTitle;

        return $this;
    }

    /**
     * Full name getter.
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Full name setter.
     *
     * @param string $full_name
     * The full name.
     *
     * @return self
     */
    public function setFullName($full_name)
    {
        $this->fullName = $full_name;

        return $this;
    }
}
