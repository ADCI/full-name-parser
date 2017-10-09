# full-name-parser

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

part(string, optional): the name of a single part to return

  - 'all' (default) = return an object containing all name parts
  - 'title' = return only the title(s) as a string (or an empty string)
  - 'first' = return only the first name as a string (or an empty string)
  - 'middle' = return only the middle name(s) as a string (or an empty string)
  - 'last' = return only the last name as a string (or an empty string)
  - 'nick' = return only the nickname(s) as a string (or an empty string)
  - 'suffix' = return only the suffix(es) as a string (or an empty string)
  - 'error' = return only the array of parsing error messages (or an empty array)

fix_case (integer, optional): fix case of output name

  - 0 or false (default) = never fix the case (retain and output same case as input name)
  - 1 or true = always fix case of output, even if input is mixed case

throws (bool|integer, optional): makes parsing errors throw PHP errors

  - 0 or false = return warnings about parsing errors, but continue
  - 1 or true (default) = if a parsing error is found, throw a PHP error

### Advanced Use

```php
<?php
use ADCI\FullNameParser\Parser;
// some code ...
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
```

## Reporting Bugs

If you find a name this function does not parse correctly, or any other bug,
please report it here: https://github.com/adci/full-name-parser/issues

## Credits and precursors

This parser is based on and compatible with [davidgorges/human-name-parser](https://github.com/davidgorges/HumanNameParser.php). 

It also implements all tests and most of additional functionality from [dschnelldavis/parse-full-name](https://github.com/dschnelldavis/parse-full-name).
