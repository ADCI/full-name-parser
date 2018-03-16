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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     *
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
     * @coversDefaultClass
     */
    public function testAcademicTitle()
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
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
     * @throws \ADCI\FullNameParser\Exception\NameParsingException
     * @coversDefaultClass
     */
    public function testNameList()
    {
        foreach (self::NAMES as $nameStr) {
            foreach ((array)$nameStr['original'] as $name) {
                $nameObject = $this->parser->parse($name);
                $error_msg = sprintf(self::OUTPUT_STR, "leading initial", $nameStr['leading'], $name);
                $this->assertEquals($nameStr['leading'], $nameObject->getLeadingInitial(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "first name", $nameStr['first'], $name);
                $this->assertEquals($nameStr['first'], $nameObject->getFirstName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "nickname", $nameStr['nick'], $name);
                $this->assertEquals($nameStr['nick'], $nameObject->getNickNames(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "middle name", $nameStr['middle'], $name);
                $this->assertEquals($nameStr['middle'], $nameObject->getMiddleName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "last name", $nameStr['last'], $name);
                $this->assertEquals($nameStr['last'], $nameObject->getLastName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "suffix", $nameStr['suffix'], $name);
                $this->assertEquals($nameStr['suffix'], $nameObject->getSuffix(), $error_msg);
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
            'original' => ['Björn O\'Malley', 'O\'Malley, Björn'],
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => '',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'Bin Lin',
            'leading' => '',
            'first' => 'Bin',
            'nick' => '',
            'middle' => '',
            'last' => 'Lin',
            'suffix' => '',
        ],
        [
            'original' => 'Linda Jones',
            'leading' => '',
            'first' => 'Linda',
            'nick' => '',
            'middle' => '',
            'last' => 'Jones',
            'suffix' => '',
        ],
        [
            'original' => 'Jason H. Priem',
            'leading' => '',
            'first' => 'Jason',
            'nick' => '',
            'middle' => 'H.',
            'last' => 'Priem',
            'suffix' => '',
        ],
        [
            'original' => 'Björn O\'Malley-Muñoz',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => '',
            'last' => 'O\'Malley-Muñoz',
            'suffix' => '',
        ],
        [
            'original' => 'Björn C. O\'Malley',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => 'C.',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => [
                'Björn "Bill" O\'Malley',
                'Björn ("Bill") O\'Malley',
                'Björn (Bill) O\'Malley',
                'Björn \'Bill\' O\'Malley',
            ],
            'leading' => '',
            'first' => 'Björn',
            'nick' => 'Bill',
            'middle' => '',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'Björn ("Wild Bill") O\'Malley',
            'leading' => '',
            'first' => 'Björn',
            'nick' => 'Wild Bill',
            'middle' => '',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'Björn C O\'Malley',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => 'C',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'Björn C. R. O\'Malley',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => 'C. R.',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'Björn Charles O\'Malley',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => 'Charles',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'Björn Charles R. O\'Malley',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => 'Charles R.',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'Björn van O\'Malley',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => '',
            'last' => 'van O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'Björn Charles van der O\'Malley',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => 'Charles',
            'last' => 'van der O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'Björn Charles O\'Malley y Muñoz',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => 'Charles',
            'last' => 'O\'Malley y Muñoz',
            'suffix' => '',
        ],
        [
            'original' => 'Björn O\'Malley, Jr.',
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => '',
            'last' => 'O\'Malley',
            'suffix' => 'Jr.',
        ],
        [
            'original' => ['Björn O\'Malley Jr', 'O\'Malley, Björn Jr'],
            'leading' => '',
            'first' => 'Björn',
            'nick' => '',
            'middle' => '',
            'last' => 'O\'Malley',
            'suffix' => 'Jr',
        ],
        [
            'original' => 'B O\'Malley',
            'leading' => '',
            'first' => 'B',
            'nick' => '',
            'middle' => '',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'William Carlos Williams',
            'leading' => '',
            'first' => 'William',
            'nick' => '',
            'middle' => 'Carlos',
            'last' => 'Williams',
            'suffix' => '',
        ],
        [
            'original' => 'C. Björn Roger O\'Malley',
            'leading' => 'C.',
            'first' => 'Björn',
            'nick' => '',
            'middle' => 'Roger',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'B. C. O\'Malley',
            'leading' => '',
            'first' => 'B.',
            'nick' => '',
            'middle' => 'C.',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'B C O\'Malley',
            'leading' => '',
            'first' => 'B',
            'nick' => '',
            'middle' => 'C',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'B.J. Thomas',
            'leading' => '',
            'first' => 'B.J.',
            'nick' => '',
            'middle' => '',
            'last' => 'Thomas',
            'suffix' => '',
        ],
        [
            'original' => 'O\'Malley, C. Björn',
            'leading' => 'C.',
            'first' => 'Björn',
            'nick' => '',
            'middle' => '',
            'last' => 'O\'Malley',
            'suffix' => '',
        ],
        [
            'original' => 'O\'Malley, C. Björn III',
            'leading' => 'C.',
            'first' => 'Björn',
            'nick' => '',
            'middle' => '',
            'last' => 'O\'Malley',
            'suffix' => 'III',
        ],
        [
            'original' => 'O\'Malley y Muñoz, C. Björn Roger III',
            'leading' => 'C.',
            'first' => 'Björn',
            'nick' => '',
            'middle' => 'Roger',
            'last' => 'O\'Malley y Muñoz',
            'suffix' => 'III',
        ],
        /* @see https://github.com/ADCI/full-name-parser/issues/8 */
        [
            'original' => 'Arantes Rodrigues, R',
            'leading' => 'R',
            'title' => '',
            'first' => 'Arantes',
            'middle' => '',
            'last' => 'Rodrigues',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
    ];
}
