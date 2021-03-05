# full-name-parser

[![Latest Stable Version](https://poser.pugx.org/adci/full-name-parser/v/stable)](https://github.com/ADCI/full-name-parser/releases/latest)
[![License](https://poser.pugx.org/adci/full-name-parser/license)](https://opensource.org/licenses/MIT)
[![Build Status](https://travis-ci.com/ADCI/full-name-parser.svg?branch=master)](https://travis-ci.com/ADCI/full-name-parser)
[![Test Coverage](https://api.codeclimate.com/v1/badges/bb07c274c3cba2c238d0/test_coverage)](https://codeclimate.com/github/ADCI/full-name-parser/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/bb07c274c3cba2c238d0/maintainability)](https://codeclimate.com/github/ADCI/full-name-parser/maintainability)

## Description

FullNameParser is designed to parse large batches of full names in multiple
inconsistent formats, as from a database, and continue processing without error,
even if given some unparsable garbage entries.

FullNameParser::parse():

1. accepts a string containing a person's full name, in any format,
2. analyzes and attempts to detect the format of that name,
3. (if possible) parses the name into its component parts, and
4. (by default) returns an object containing all individual parts of the name:
    - title (string): title(s) (e.g. "Ms." or "Dr.")
    - first (string): first name or initial
    - middle (string): middle name(s) or initial(s)
    - last (string): last name or initial
    - nick (string): nickname(s)
    - suffix (string): suffix(es) (e.g. "Jr.", "II", or "Esq.")
    - error (array of strings): any parsing error messages

Optionally, FullNameParser can also:

* return only the specified part of a name as a string (or errors as an array)
* always fix or ignore the letter case of the returned parts (the default is
    to fix the case only when the original input is all upper or all lowercase)
* stop on errors (the default is to return warning messages in the output,
    but never throw a PHP error, no matter how mangled the input)
    
Now FullNameParser cannot:
* detect more variations of name prefixes, suffixes, and titles (the default
    detects 29 prefixes, 19 suffixes, 16 titles, and 8 conjunctions, but in future it
    can be set to detect 97 prefixes, 23 suffixes, and 204 titles instead)

If this is not what you're looking for, is overkill for your application, or
is in the wrong language, check the "Credits" section at the end of this file
for links to other parsers which may suit your needs better.

## Use

### Basic Use

```php
<?php
use ADCI\FullNameParser\Parser;
// some code ...
$parser = new Parser();

$name = 'Mr. David Davis';
$nameObject = $parser->parse($name);

$this->assertEquals('Mr.', $nameObject->getAcademicTitle());
$this->assertEquals('David', $nameObject->getFirstName());
$this->assertEquals('Davis', $nameObject->getLastName());
```

### Advanced options

suffixes(array of string, optional): Override list of suffixes to search.
  These suffixes can end with dot in source name string.
  Though you must not include dots in these suffixes, dots are handled automatically on parsing.

numeral_suffixes(array of string, optional): Override list of numeral suffixes to search.
  Do not contain trailing dots.

prefixes(array of string, optional): Override list of prefixes to search.

academic_titles(array of string, optional): Override list of academic titles to search.

part(string, optional): The name of a single part to return.

  - 'all' (default) = Return an object containing all name parts.
  - 'title' = Return only the title(s) as a string (or an empty string).
  - 'first' = Return only the first name as a string (or an empty string).
  - 'middle' = Return only the middle name(s) as a string (or an empty string).
  - 'last' = Return only the last name as a string (or an empty string).
  - 'nick' = Return only the nickname(s) as a string (or an empty string).
  - 'suffix' = Return only the suffix(es) as a string (or an empty string).
  - 'error' = Return only the array of parsing error messages (or an empty array).

fix_case (bool|integer, optional): Fix case of output name.

  - 0 or false (default) = Never fix the case (retain and output same case as input name).
  - 1 or true = Always fix case of output, even if input is mixed case.

throws (bool|integer, optional): Makes parsing errors throw PHP errors.

  - 0 or false = Return warnings about parsing errors, but continue.
  - 1 or true (default) = If a parsing error is found, throw a PHP error.

mandatory_first_name(bool|integer, optional): Throw error if first name not found.

  - 0 or false = Does not throw error.
  - 1 or true (default) = Throw error.

mandatory_last_name(bool|integer, optional): Throw error if last name not found.

  - 0 or false = Does not throw error.
  - 1 or true (default) = Throw error.

mandatory_middle_name(bool|integer, optional): Throw warning if a lot of middle names.

  - 0 or false = Does not throw warning.
  - 1 or true (default) = Throw warning.

### Advanced Use

```php
<?php
use ADCI\FullNameParser\Parser;
// Some code...
// Example with advanced options.
$parser = new Parser(['part' => 'all', 'fix_case' => TRUE, 'throws' => FALSE]);
$name = 'DE LORENZO Y GUTIEREZ, Mr. JÜAN MARTINEZ (MARTIN) Jr.';
$nameObject = $parser->parse($name);

$this->assertEquals('Mr.', $nameObject->getAcademicTitle());
$this->assertEquals('Jüan', $nameObject->getFirstName());
$this->assertEquals('Martinez', $nameObject->getMiddleName());
$this->assertEquals('de Lorenzo y Gutierez', $nameObject->getLastName());
$this->assertEquals('Martin', $nameObject->getNicknames());
$this->assertEquals('Jr.', $nameObject->getSuffix());
$this->assertEquals([], $nameObject->getErrors());
// Some else code...
// And example with overriding suffixes and titles lists by short versions of lists.
$options = [
    'suffixes' => ['esq', 'jr', 'sr'],
    'academic_titles' => ['ms', 'mrs', 'mr'],
];
$parser = new Parser($options);
$nameObject = $parser->parse($name);
```

## Reporting Bugs

If you find a name this function does not parse correctly, or any other bug,
please report it here: https://github.com/adci/full-name-parser/issues

## Credits and precursors

This parser is based on and compatible with [davidgorges/human-name-parser](https://github.com/davidgorges/HumanNameParser.php). 

It also implements all tests and most of additional functionality from [dschnelldavis/parse-full-name](https://github.com/dschnelldavis/parse-full-name).
