<?php

/**
 * @file
 * Split a single name string into it's name parts (first name, last name,
 *   titles, middle names)
 */

namespace ADCI\FullNameParser;

use ADCI\FullNameParser\Exception\FirstNameNotFoundException;
use ADCI\FullNameParser\Exception\IncorrectInputException;
use ADCI\FullNameParser\Exception\LastNameNotFoundException;
use ADCI\FullNameParser\Exception\ManyMiddleNamesException;
use ADCI\FullNameParser\Exception\NameParsingException;

/**
 * Class Parser.
 *
 * @package FullNameParser
 */
class Parser
{

    // <editor-fold desc="Const section.">

    /*
     * The regex use is a bit tricky.  *Everything* matched by the regex will be replaced,
     * but you can select a particular parenthesized submatch to be returned.
     * Also, note that each regex requires that the preceding ones have been run, and matches chopped out.
     */

    /**
     * Parts with surrounding punctuation as nicknames.
     *
     * @var string
     */
    const REGEX_NICKNAMES = "/([\[('‘“\"]+)(.+?)(['’”\"\])]+)/";

    /**
     * Regex for titles.
     *
     * @var string
     */
    const REGEX_TITLES = "/(%s)\.*/";

    // @todo Maybe it can be useful to find suffix with some regex.
    /*const REGEX_SUFFIX = "/(%s)$/";/**/
    /*const REGEX_SUFFIX = "/(%s) +/";/**/

    /**
     * Regex for last name.
     *
     * @var string
     */
    const REGEX_LAST_NAME = "/(?!^)\b([^ ]+ y |%s)*[^ ]+$/i";

    /**
     * Regex for initials.
     * Note the lookahead, which isn't returned or replaced.
     *
     * @var string
     */
    const REGEX_LEADING_INITIAL = "/^(.\.*)(?= \p{L}{2})/";

    /**
     * Regex for first name.
     *
     * @var string
     */
    const REGEX_FIRST_NAME = "/^[^ ]+/";

    /**
     * List of possible suffixes.
     *
     * @var array
     */
    const SUFFIXES = [
      'esq',
      'esquire',
      'jr',
      'sr',
      '2',
      'iii',
      'ii',
      'iv',
      'phd',
    ];

    /**
     * List of possible prefixes.
     *
     * @var array
     */
    const PREFIXES = [
      'bar',
      'ben',
      'bin',
      'da',
      'dal',
      'de la',
      'de',
      'del',
      'der',
      'di',
      'ibn',
      'la',
      'le',
      'san',
      'st',
      'ste',
      'van der',
      'van den',
      'van',
      'vel',
      'von',
    ];

    /**
     * List of normal cased suffixes.
     *
     * @var array
     */
    const FORCED_CASE = [
      'e',
      'y',
      'av',
      'af',
      'da',
      'dal',
      'de',
      'del',
      'der',
      'di',
      'la',
      'le',
      'van',
      'der',
      'den',
      'vel',
      'von',
      'II',
      'III',
      'IV',
      'J.D.',
      'LL.M.',
      'M.D.',
      'D.O.',
      'D.C.',
      'Ph.D.',
    ];

    /**
     * List of possible titles.
     *
     * @var array
     */
    const TITLES = ['ms', 'miss', 'mrs', 'mr', 'prof', 'dr'];

    /**
     * List of possible parts.
     *
     * @var array
     */
    const PARTS = [
      'title',
      'first',
      'middle',
      'last',
      'nick',
      'suffix',
      'error',
    ];

    /**
     * Return 'all' part by default.
     *
     * @var string
     */
    const PART = 'all';

    /**
     * Doesn't fix case by default.
     *
     * @var bool
     */
    const FIX_CASE = false;

    /**
     * Throw error by default.
     *
     * @var bool
     */
    const THROWS = true;

    // </editor-fold>

    // <editor-fold desc="Private vars section.">

    /**
     * Array of string possible suffixes.
     *
     * @var array
     */
    private $suffixes;

    /**
     * Array of string possible prefixes.
     *
     * @var array
     */
    private $prefixes;

    /**
     * Array of string possible titles.
     *
     * @var array
     */
    private $academic_titles;

    /**
     * Temporary variable of non-parsed name part.
     *
     * @var string
     */
    private $name_token;

    /**
     * Throw error if first name not found.
     *
     * @var boolean
     */
    private $mandatory_first_name = true;

    /**
     * Throw error if last name not found.
     *
     * @var boolean
     */
    private $mandatory_last_name = true;

    /**
     * Object which contains parsed name parts.
     *
     * @var \ADCI\FullNameParser\Name
     */
    private $name;

    /**
     * Input name string.
     *
     * @var string
     */
    private $original_name;

    /**
     * Name of part to return for.
     *
     * @var string
     */
    private $name_part;

    /**
     * Throw error if true.
     *
     * @var bool
     */
    private $stop_on_error;

    /**
     * Fix name case if true.
     *
     * @var bool
     */
    private $fix_case;

    // </editor-fold>

    /**
     * Parser constructor.
     *
     * Parameter $options is array of options with next keys possible:
     * - 'suffixes' for an array of suffixes.
     * - 'prefix' for an array of prefixes.
     * - 'academic_titles' for an array of titles.
     * - 'mandatory_first_name' bool. Throw error if first name not found.
     * - 'mandatory_last_name' bool. Throw error if last name not found.
     * - 'part' string. Name part to return. Default 'all'.
     * - 'fix_case' bool. Make name parts uppercase first letter. Default false.
     * - 'throws' bool. Stop on errors. Default true.
     *
     * @param array $options
     * Array of options. See method description for possible values.
     */
    public function __construct($options = [])
    {
        $options += [
          'suffixes' => self::SUFFIXES,
          'prefixes' => self::PREFIXES,
          'academic_titles' => self::TITLES,
          'part' => self::PART,
          'fix_case' => self::FIX_CASE,
          'throws' => self::THROWS,
        ];
        if (array_search(strtolower($options['part']), self::PARTS) === false) {
            $options['part'] = self::PART;
        }
        if (isset($options['mandatory_first_name'])) {
            $this->mandatory_first_name = (boolean)$options['mandatory_first_name'];
        }
        if (isset($options['mandatory_last_name'])) {
            $this->mandatory_last_name = (boolean)$options['mandatory_last_name'];
        }

        $this->setStopOnError($options['throws'] == true);
        $this->setFixCase($options['fix_case'] == true);
        $this->setNamePart(strtolower($options['part']));
        $this->setSuffixes($options['suffixes']);
        $this->setPrefixes($options['prefixes']);
        $this->setAcademicTitles($options['academic_titles']);
    }

    /**
     * Parse the name into its constituent parts.
     *
     * @param string|mixed|null $name
     * String to parse.
     *
     * @return \ADCI\FullNameParser\Name|string $name
     * Parsed name object or part of it.
     */
    public function parse($name)
    {
        $this->name = new Name();
        if (is_string($name)) {
            $words = explode(' ', $name);
            $casedName = [];
            foreach ($words as $word) {
                $casedName[] = $this->fixParsedNameCase($word);
            }
            $name = implode(' ', $casedName);
            $this->original_name = $name;
            // Each suffix gets a "\.*" behind it.
            $suffixes = implode("\.*|", $this->getSuffixes()) . "\.*";
            // Each prefix gets a " " behind it.
            $prefixes = implode(" |", $this->getPrefixes()) . " ";
            // Each title gets a "\.*" behind it.
            $academicTitles = implode("\.+|", $this->getAcademicTitles()) . "\.+";

            $this->name_token = $name;

            $this->findAcademicTitle($academicTitles);
            $this->findNicknames();

            $this->findSuffix($suffixes);
            $this->flipNameToken();

            $this->findLastName($prefixes);
            $this->findLeadingInitial();
            $this->findFirstName();
            $this->findMiddleName();

            return $this->name->getPart($this->getNamePart());
        }
        $this->handleError(new IncorrectInputException());
        return $this->name->getPart($this->getNamePart());
    }

    /**
     * Throw exception if set in options.
     *
     * @param \ADCI\FullNameParser\Exception\NameParsingException $ex
     * Error to throw or add to error array.
     *
     * @return self
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
     */
    private function handleError(NameParsingException $ex)
    {
        $this->name->addError($ex);
        if ($this->isStopOnError()) {
            if ($ex instanceof ManyMiddleNamesException) {
                trigger_error($ex, E_USER_WARNING);
            } else {
                throw $ex;
            }
        }
        return $this;
    }

    /**
     * Makes each word in name string ucfirst.
     *
     * @param string $word
     *
     * @return string
     */
    private function fixParsedNameCase($word)
    {
        if ($this->isFixCase()) {
            $forceCaseList = self::FORCED_CASE;
            $in_list = false;
            foreach ($forceCaseList as $item) {
                if (strtolower($word) === strtolower($item)) {
                    $in_list |= strtolower($word) === strtolower($item);
                    $word = $item;
                }
            }
            if (!$in_list) {
                $word = ucfirst(mb_strtolower($word));
            }
        }
        return $word;
    }

    /**
     * Find and add academic title to Name object.
     *
     * @param string $academicTitles
     * Regex to find titles.
     *
     * @return self
     */
    private function findAcademicTitle($academicTitles)
    {
        $regex = sprintf(self::REGEX_TITLES, $academicTitles);
        $title = $this->findWithRegex($regex, 1);
        if ($title) {
            $this->name->setAcademicTitle($title);
            $this->name_token = str_ireplace($title, "", $this->name_token);
        }

        return $this;
    }

    /**
     * Find and add nicknames to Name object.
     *
     * @return self
     */
    private function findNicknames()
    {
        $nicknames = $this->findWithRegex(self::REGEX_NICKNAMES, 2);
        if ($nicknames) {
            // Need to fix case because first char was bracket or quote.
            $this->name->setNicknames($this->fixParsedNameCase($nicknames));
            $this->removeTokenWithRegex(self::REGEX_NICKNAMES);
        }

        return $this;
    }

    /**
     * Find and add suffixes to Name object.
     *
     * @param string $suffixes
     * The suffixes to be searched for.
     *
     * @return self
     */
    private function findSuffix($suffixes)
    {
        $regex = "/($suffixes)/";
        $suffix = $this->findWithRegex($regex, 1);
        if ($suffix) {
            $this->name->setSuffix($suffix);
            $this->removeTokenWithRegex($regex);
        }
        $extra = $this->findExtraSuffix('');
        $known = $this->name->getSuffix();
        $known .= $known ? ', ' : '';
        if ($extra !== '') {
            $this->name->setSuffix($known . $extra);
        }

        return $this;
    }

    /**
     * Function for search extra suffixes.
     * From the end string each additional comma separated word.
     *
     * @todo Refactor to regexp non-recursive function. Something like this /,+ +([^, ]+)/
     *
     * @param string $extra
     * Founded suffixes.
     *
     * @return string
     * Result string of suffixes.
     */
    private function findExtraSuffix($extra)
    {
        // There's no need prefixes in suffix list.
        // Each prefix gets a " " behind it.
        $prefixes = '/(' . implode(" |", $this->getPrefixes()) . " )+/ui";
        $explodeCommas = explode(',', $this->name_token);
        $explodeSpaces = explode(' ', preg_replace($prefixes, '', trim($this->name_token)));
        // If you have more than 1 commas and each word from the end separate commas - it is extra suffixes.
        if (count($explodeSpaces) > 2 && trim(end($explodeCommas)) === trim(end($explodeSpaces), ',')) {
            $suffix = trim(end($explodeCommas));
            $extra = $extra === '' ? '' : ', ' . $extra;
            $extra = $suffix . $extra;
            $regex = "/($suffix)/";
            $this->removeTokenWithRegex($regex);
            $extra = $this->findExtraSuffix($extra);
        }

        return $extra;
    }

    /**
     * Find and add last name to Name object.
     *
     * @param string $prefixes
     * Regex to find prefixes.
     *
     * @return self
     */
    private function findLastName($prefixes)
    {
        $regex = sprintf(self::REGEX_LAST_NAME, $prefixes);
        $lastName = $this->findWithRegex($regex, 0);
        if ($lastName) {
            $this->name->setLastName($lastName);
            $this->removeTokenWithRegex($regex);
        } elseif ($this->mandatory_last_name) {
            $this->handleError(new LastNameNotFoundException());
        }

        return $this;
    }

    /**
     * Find and add first name to Name object.
     *
     * @return self
     */
    private function findFirstName()
    {
        $lastName = $this->findWithRegex(self::REGEX_FIRST_NAME, 0);
        if ($lastName) {
            $this->name->setFirstName($lastName);
            $this->removeTokenWithRegex(self::REGEX_FIRST_NAME);
        } elseif ($this->mandatory_first_name) {
            $this->handleError(new FirstNameNotFoundException());
        }

        return $this;
    }

    /**
     * Find and add leading initial to Name object.
     *
     * @return self
     */
    private function findLeadingInitial()
    {
        $leadingInitial = $this->findWithRegex(self::REGEX_LEADING_INITIAL, 1);
        if ($leadingInitial) {
            $this->name->setLeadingInitial($leadingInitial);
            $this->removeTokenWithRegex(self::REGEX_LEADING_INITIAL);
        }

        return $this;
    }

    /**
     * Find and add middle name to Name object.
     *
     * @return self
     */
    private function findMiddleName()
    {
        $middleName = trim($this->name_token);
        $count = count(explode(' ', $middleName));
        if ($count > 2) {
            $this->handleError(new ManyMiddleNamesException('Warning: ' . $count . ' middle names'));
        }
        if ($middleName) {
            $this->name->setMiddleName($middleName);
        }

        return $this;
    }

    /**
     * Find and return part of name for regex.
     *
     * @param string $regex
     * Regex to search.
     * @param int $submatchIndex
     * Index of regex part.
     *
     * @return string|bool
     * Founded part of name. False if not found.
     */
    private function findWithRegex($regex, $submatchIndex = 0)
    {
        // unicode + case-insensitive
        $regex = $regex . "ui";
        preg_match($regex, $this->name_token, $match);
        $subset = (isset($match[$submatchIndex])) ? $match[$submatchIndex] : false;

        return $subset;
    }

    /**
     * Remove founded part from name string.
     *
     * @param string $regex
     * Regex to remove name part.
     *
     * @return self
     */
    private function removeTokenWithRegex($regex)
    {
        $numReplacements = 0;
        $tokenRemoved = preg_replace($regex . 'ui', ' ', $this->name_token, -1, $numReplacements);
        if ($numReplacements > 1) {
            // @todo: unused code line?
            $this->handleError(new NameParsingException("The regex being used has multiple matches."));
        }

        $this->name_token = $this->normalize($tokenRemoved);

        return $this;
    }

    /**
     * Removes extra whitespace and punctuation from string
     * Strips whitespace chars from ends, strips redundant whitespace, converts
     * whitespace chars to " ".
     *
     * @param string $taintedString
     * String to normalize.
     *
     * @return string
     * Normalized string.
     */
    private function normalize($taintedString)
    {
        // Remove any kind of invisible character from the start.
        $taintedString = preg_replace("#^\s*#u", "", $taintedString);
        // Remove any kind of invisible character from the end.
        $taintedString = preg_replace("#\s*$#u", "", $taintedString);
        // Add exception so that non-breaking space characters are not stripped during norm function.
        if (substr_count($taintedString, "\xc2\xa0") == 0) {
            // Replace any kind of invisible character in string to whitespace.
            $taintedString = preg_replace("#\s+#u", " ", $taintedString);
        }
        // Replace two commas to one.
        $taintedString = preg_replace("(, ?, ?)", ", ", $taintedString);
        // Remove commas and spaces from the end of string.
        $taintedString = rtrim($taintedString, " ,");

        return $taintedString;
    }

    /**
     * Flip name around comma.
     *
     * @return self
     */
    private function flipNameToken()
    {
        $this->name_token = $this->flipStringPartsAround($this->name_token, ",");
        return $this;
    }

    /**
     * Flips the front and back parts of a name with one another.
     * Front and back are determined by a specified character somewhere in the
     * middle of the string.
     *
     * @param string $string
     * String to flip.
     * @param string $char
     * Char to flip around for.
     *
     * @return string
     * Flipped string.
     */
    private function flipStringPartsAround($string, $char)
    {
        $substrings = preg_split("/$char/u", $string);

        if (count($substrings) == 2) {
            $string = $substrings[1] . " " . $substrings[0];
            $string = $this->normalize($string);
        } elseif (count($substrings) > 2) {
            $error_message = "Can't flip around multiple '$char' characters in name string '$this->original_name'.";
            $this->handleError(new NameParsingException($error_message));
        }

        return $string;
    }

    // <editor-fold desc="Getter/Setter section.">

    /**
     * Suffixes getter.
     *
     * @return array
     */
    public function getSuffixes()
    {
        return $this->suffixes;
    }

    /**
     * Suffixes setter.
     *
     * @param array $suffixes
     * The suffixes to set.
     *
     * @return self
     */
    public function setSuffixes($suffixes)
    {
        $this->suffixes = $suffixes;
        return $this;
    }

    /**
     * Prefixes getter.
     *
     * @return array
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * Prefixes setter.
     *
     * @param array $prefixes
     * The prefixes.
     *
     * @return self
     */
    public function setPrefixes($prefixes)
    {
        $this->prefixes = $prefixes;
        return $this;
    }

    /**
     * Titles getter.
     *
     * @return array
     */
    public function getAcademicTitles()
    {
        return $this->academic_titles;
    }

    /**
     * Titles setter.
     *
     * @param array $academicTitles
     * The academic titles.
     *
     * @return self
     */
    public function setAcademicTitles($academicTitles)
    {
        $this->academic_titles = $academicTitles;
        return $this;
    }

    /**
     * Name part getter.
     *
     * @return string
     */
    public function getNamePart()
    {
        return $this->name_part;
    }

    /**
     * Name part setter.
     *
     * @param string $namePart
     * Name of part of name to return.
     *
     * @return self
     */
    public function setNamePart($namePart)
    {
        $this->name_part = $namePart;
        return $this;
    }

    /**
     * Stop on error getter.
     *
     * @return bool
     */
    public function isStopOnError()
    {
        return $this->stop_on_error;
    }

    /**
     * Stop on error setter.
     *
     * @param bool $stopOnError
     * Stop when get parse error.
     *
     * @return self
     */
    public function setStopOnError($stopOnError)
    {
        $this->stop_on_error = $stopOnError;
        return $this;
    }

    /**
     * Fix case getter.
     *
     * @return bool
     */
    public function isFixCase()
    {
        return $this->fix_case;
    }

    /**
     * Fix case setter.
     *
     * @param bool $fixCase
     * Fix case when parse.
     *
     * @return self
     */
    public function setFixCase($fixCase)
    {
        $this->fix_case = $fixCase;
        return $this;
    }

    // </editor-fold>
}
