<?php

namespace ADCI\FullNameParser\Test;

use ADCI\FullNameParser\Parser;
use PHPUnit\Framework\TestCase;

/**
 * Test case based on https://github.com/davidgorges/HumanNameParser.php .
 *
 * @coversDefaultClass \ADCI\FullNameParser\Parser
 * @group FullNameParser
 */
class NameTest extends TestCase
{

    /**
     * Assert error message.
     *
     * @var string
     */
    const OUTPUT_STR = "failed to ensure correct %s (%s) in name %s";

    /**
     * Parser variable.
     *
     * @var \ADCI\FullNameParser\Parser
     */
    private $parser;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    /**
     * Simple test on suffix parsing.
     *
     * @coversDefaultClass
     */
    public function testSuffix()
    {
        $name = 'Björn O\'Malley, Jr.';
        $nameObject = $this->parser->parse($name);
        $this->assertEquals('O\'Malley', $nameObject->getLastName());
        $this->assertEquals('Björn', $nameObject->getFirstName());
        $this->assertEquals('Jr.', $nameObject->getSuffix());
    }

    /**
     * Simple parsing test.
     * @coversDefaultClass
     */
    public function testSimple()
    {
        $name = 'Hans Meiser';
        $nameObject = $this->parser->parse($name);
        $this->assertEquals('Hans', $nameObject->getFirstName());
        $this->assertEquals('Meiser', $nameObject->getLastName());
    }

    /**
     * Simple parsing test with comma.
     *
     * @coversDefaultClass
     */
    public function testReverse()
    {
        $name = 'Meiser, Hans';
        $nameObject = $this->parser->parse($name);
        $this->assertEquals('Hans', $nameObject->getFirstName());
        $this->assertEquals('Meiser', $nameObject->getLastName());
    }

    /**
     * Simple parsing test with comma and title.
     *
     * @coversDefaultClass
     */
    public function testReverseWithAcademicTitle()
    {
        $name = 'Dr. Meiser, Hans';
        $nameObject = $this->parser->parse($name);
        $this->assertEquals('Dr.', $nameObject->getAcademicTitle());
        $this->assertEquals('Meiser', $nameObject->getLastName());
        $this->assertEquals('Hans', $nameObject->getFirstName());
    }

    /**
     * Simple parsing test with title.
     *
     * @coversDefaultClass
     */
    public function testithAcademicTitle()
    {
        $name = 'Dr. Hans Meiser';
        $nameObject = $this->parser->parse($name);
        $this->assertEquals('Dr.', $nameObject->getAcademicTitle());
        $this->assertEquals('Meiser', $nameObject->getLastName());
        $this->assertEquals('Hans', $nameObject->getFirstName());
    }

    /**
     * Simple parsing test with prefix.
     *
     * @coversDefaultClass
     */
    public function testLastNameWithPrefix()
    {
        $name = 'Björn van Olst';
        $nameObject = $this->parser->parse($name);
        $this->assertEquals('van Olst', $nameObject->getLastName());
        $this->assertEquals('Björn', $nameObject->getFirstName());
    }

    /**
     * Exception test.
     *
     * @expectedException \ADCI\FullNameParser\Exception\FirstNameNotFoundException
     * @covers \ADCI\FullNameParser\Exception\FirstNameNotFoundException
     */
    public function testNoFirstNameDefaultException()
    {
        $name = 'Mr. Hyde';
        $this->parser->parse($name);
    }

    /**
     * Exception test.
     *
     * @expectedException \ADCI\FullNameParser\Exception\LastNameNotFoundException
     * @covers \ADCI\FullNameParser\Exception\LastNameNotFoundException
     */
    public function testNoLastNameDefaultException()
    {
        $name = 'Edward';
        $this->parser->parse($name);
    }

    /**
     * Simple last name parsing test.
     *
     * @coversDefaultClass
     */
    public function testFirstNameNotMandatory()
    {
        $this->parser = new Parser(['mandatory_first_name' => false]);
        $name = 'Dr. Jekyll';
        $nameObject = $this->parser->parse($name);
        $this->assertEquals('Dr.', $nameObject->getAcademicTitle());
        $this->assertEquals('Jekyll', $nameObject->getLastName());
    }

    /**
     * Simple first name parsing test.
     *
     * @coversDefaultClass
     */
    public function testLastNameNotMandatory()
    {
        $this->parser = new Parser(['mandatory_last_name' => false]);
        $name = 'Henry';
        $nameObject = $this->parser->parse($name);
        $this->assertEquals('Henry', $nameObject->getFirstName());
    }

    /**
     * Exception test.
     *
     * @expectedException \ADCI\FullNameParser\Exception\FirstNameNotFoundException
     * @covers \ADCI\FullNameParser\Exception\FirstNameNotFoundException
     */
    public function testFirstNameMandatory()
    {
        $this->parser = new Parser(['mandatory_first_name' => true]);
        $name = 'Mr. Hyde';
        $this->parser->parse($name);
    }

    /**
     * Exception test.
     *
     * @expectedException \ADCI\FullNameParser\Exception\LastNameNotFoundException
     * @covers \ADCI\FullNameParser\Exception\LastNameNotFoundException
     */
    public function testLastNameMandatory()
    {
        $this->parser = new Parser(['mandatory_last_name' => true]);
        $name = 'Edward';
        $this->parser->parse($name);
    }

    /**
     * Complex name parsing test.
     *
     * @coversDefaultClass
     */
    public function testNameList()
    {
        foreach (self::NAMES as $nameStr) {
            $nameArr = (array)$nameStr[0];
            foreach ($nameArr as $name) {
                $nameObject = $this->parser->parse($name);
                $error_msg = sprintf(self::OUTPUT_STR, "leading initial", $nameStr[1][0], $name);
                $this->assertEquals($nameStr[1][0], $nameObject->getLeadingInitial(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "first name", $nameStr[1][1], $name);
                $this->assertEquals($nameStr[1][1], $nameObject->getFirstName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "nickname", $nameStr[1][2], $name);
                $this->assertEquals($nameStr[1][2], $nameObject->getNickNames(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "middle name", $nameStr[1][3], $name);
                $this->assertEquals($nameStr[1][3], $nameObject->getMiddleName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "last name", $nameStr[1][4], $name);
                $this->assertEquals($nameStr[1][4], $nameObject->getLastName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "suffix", $nameStr[1][5], $name);
                $this->assertEquals($nameStr[1][5], $nameObject->getSuffix(), $error_msg);
            }
        }
    }

    /**
     * Test case for complex parsing.
     *
     * @var array
     */
    const NAMES = [
      [
        ['Björn O\'Malley', 'O\'Malley, Björn'],
        ['', 'Björn', '', '', 'O\'Malley', ''],
      ],
      ['Bin Lin', ['', 'Bin', '', '', 'Lin', '']],
      ['Linda Jones', ['', 'Linda', '', '', 'Jones', '']],
      ['Jason H. Priem', ['', 'Jason', '', 'H.', 'Priem', '']],
      ['Björn O\'Malley-Muñoz', ['', 'Björn', '', '', 'O\'Malley-Muñoz', '']],
      ['Björn C. O\'Malley', ['', 'Björn', '', 'C.', 'O\'Malley', '']],
      [
        [
          'Björn "Bill" O\'Malley',
          'Björn ("Bill") O\'Malley',
          'Björn (Bill) O\'Malley',
          'Björn \'Bill\' O\'Malley',
        ],
        ['', 'Björn', 'Bill', '', 'O\'Malley', ''],
      ],
      [
        'Björn ("Wild Bill") O\'Malley',
        ['', 'Björn', 'Wild Bill', '', 'O\'Malley', ''],
      ],
      ['Björn C O\'Malley', ['', 'Björn', '', 'C', 'O\'Malley', '']],
      ['Björn C. R. O\'Malley', ['', 'Björn', '', 'C. R.', 'O\'Malley', '']],
      ['Björn Charles O\'Malley', ['', 'Björn', '', 'Charles', 'O\'Malley', '']],
      [
        'Björn Charles R. O\'Malley',
        ['', 'Björn', '', 'Charles R.', 'O\'Malley', ''],
      ],
      ['Björn van O\'Malley', ['', 'Björn', '', '', 'van O\'Malley', '']],
      [
        'Björn Charles van der O\'Malley',
        ['', 'Björn', '', 'Charles', 'van der O\'Malley', ''],
      ],
      [
        'Björn Charles O\'Malley y Muñoz',
        ['', 'Björn', '', 'Charles', 'O\'Malley y Muñoz', ''],
      ],
      ['Björn O\'Malley, Jr.', ['', 'Björn', '', '', 'O\'Malley', 'Jr.']],
      [
        ['Björn O\'Malley Jr', 'O\'Malley, Björn Jr'],
        ['', 'Björn', '', '', 'O\'Malley', 'Jr'],
      ],
      ['B O\'Malley', ['', 'B', '', '', 'O\'Malley', '']],
      ['William Carlos Williams', ['', 'William', '', 'Carlos', 'Williams', '']],
      ['C. Björn Roger O\'Malley', ['C.', 'Björn', '', 'Roger', 'O\'Malley', '']],
      ['B. C. O\'Malley', ['', 'B.', '', 'C.', 'O\'Malley', '']],
      ['B C O\'Malley', ['', 'B', '', 'C', 'O\'Malley', '']],
      ['B.J. Thomas', ['', 'B.J.', '', '', 'Thomas', '']],
      ['O\'Malley, C. Björn', ['C.', 'Björn', '', '', 'O\'Malley', '']],
      ['O\'Malley, C. Björn III', ['C.', 'Björn', '', '', 'O\'Malley', 'III']],
      [
        'O\'Malley y Muñoz, C. Björn Roger III',
        ['C.', 'Björn', '', 'Roger', 'O\'Malley y Muñoz', 'III'],
      ],
    ];
}
