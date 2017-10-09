<?php

namespace ADCI\FullNameParser\Test;

use ADCI\FullNameParser\Name;
use ADCI\FullNameParser\Parser;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Error_Warning;

/**
 * Test case based on https://github.com/dschnelldavis/parse-full-name .
 *
 * @coversDefaultClass \ADCI\FullNameParser\Parser
 * @group bibcite
 */
class NameParserTest extends TestCase
{

    /**
     * Parser variable.
     *
     * @var \ADCI\FullNameParser\Parser
     */
    private $parser;

    /**
     * Assert error message.
     *
     * @var string
     */
    const OUTPUT_STR = "failed to ensure correct %s (%s) in name %s";

    /**
     * Lists of names.
     * Switch test case.
     *
     * @var array
     */
    const CASE_NAMES = [
        // Implemented.
      [
        [
            // Switch to normal case by default in this case test. Parser not switch case by default.
          'MR. JÜAN MARTINEZ (MARTIN) DE LORENZO Y GUTIEREZ JR.',
          'mr. jüan martinez (martin) de lorenzo y gutierez jr.',
        ],
        ['Mr.', 'Jüan', 'Martinez', 'de Lorenzo y Gutierez', 'Martin', 'Jr.', []],
      ],/**/
      [
        [
            // Or not switch case if not need.
            // Switch option must be set false.
          'Mr. JÜAN MARTINEZ (MARTIN) DE LORENZO Y GUTIEREZ Jr.',
        ],
        ['Mr.', 'JÜAN', 'MARTINEZ', 'DE LORENZO Y GUTIEREZ', 'MARTIN', 'Jr.', []],
        false,
      ],/**/
      [
        [
            // Switch case if need.
            // Switch option must be set true.
          'Mr. JÜAN MARTINEZ (MARTIN) DE LORENZO Y GUTIEREZ JR.',
        ],
        ['Mr.', 'Jüan', 'Martinez', 'de Lorenzo y Gutierez', 'Martin', 'Jr.', []],
        true,
      ],/**/
      [
        [
            // Or not switch case if not need.
            // Switch option must be set false.
          'mr. jüan martinez (martin) de lorenzo y gutierez jr.',
        ],
        ['mr.', 'jüan', 'martinez', 'de lorenzo y gutierez', 'martin', 'jr.', []],
        false,
      ],/**/
    ];

    /**
     * Lists of names.
     * Test case for complex parsing.
     *
     * @var array
     */
    const NAMES = [
      [['David Davis', 'Davis, David'], ['', 'David', '', 'Davis', '', '', []]],
      [['Gerald Böck', 'Böck, Gerald'], ['', 'Gerald', '', 'Böck', '', '', []]],
      [
        ['David William Davis', 'Davis, David William'],
        ['', 'David', 'William', 'Davis', '', '', []],
      ],
      [
        ['Vincent Van Gogh', 'Van Gogh, Vincent'],
        ['', 'Vincent', '', 'Van Gogh', '', '', []],
      ],
      [
        ['Lorenzo de Médici', 'de Médici, Lorenzo'],
        ['', 'Lorenzo', '', 'de Médici', '', '', []],
      ],
      [
        ['Jüan de la Véña', 'de la Véña, Jüan'],
        ['', 'Jüan', '', 'de la Véña', '', '', []],
      ],
      [
        [
          'Jüan Martinez de Lorenzo y Gutierez',
          'de Lorenzo y Gutierez, Jüan Martinez',
        ],
        ['', 'Jüan', 'Martinez', 'de Lorenzo y Gutierez', '', '', []],
      ],
      [
        [
          'Orenthal James "O. J." Simpson',
          'Orenthal \'O. J.\' James Simpson',
            // Fixed.
          '(O. J.) Orenthal James Simpson',
            // Fixed.
          'Simpson, Orenthal James "O. J."',/**/
            // Implemented.
          'Simpson, Orenthal ‘O. J.’ James',/**/
          'Simpson, [O. J.] Orenthal James',/**/
        ],
        ['', 'Orenthal', 'James', 'Simpson', 'O. J.', '', []],
      ],
      [
        ['Sammy Davis, Jr.', 'Davis, Sammy, Jr.'],
        ['', 'Sammy', '', 'Davis', '', 'Jr.', []],
      ],
        // Implemented.
      [
        [
          'John P. Doe-Ray, Jr., CLU, CFP, LUTC',
          'Doe-Ray, John P., Jr., CLU, CFP, LUTC',
        ],
        ['', 'John', 'P.', 'Doe-Ray', '', 'Jr., CLU, CFP, LUTC', []],
      ],/**/
      [
        [
          'Dr. John P. Doe-Ray, Jr.',
          'Dr. Doe-Ray, John P., Jr.',
            // Fixed.
          'Doe-Ray, Dr. John P., Jr.',/**/
        ],
        ['Dr.', 'John', 'P.', 'Doe-Ray', '', 'Jr.', []],
      ],
      [
        [
          'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
            // Fixed.
          'de Lorenzo y Gutierez, Mr. Jüan Martinez (Martin) Jr.',
          'de Lorenzo y Gutierez, Mr. Jüan (Martin) Martinez Jr.',/**/
          'Mr. de Lorenzo y Gutierez, Jüan Martinez (Martin) Jr.',
          'Mr. de Lorenzo y Gutierez, Jüan (Martin) Martinez Jr.',
            // Fixed.
          'Mr. de Lorenzo y Gutierez Jr., Jüan Martinez (Martin)',
          'Mr. de Lorenzo y Gutierez Jr., Jüan (Martin) Martinez',
          'Mr. de Lorenzo y Gutierez, Jr. Jüan Martinez (Martin)',
          'Mr. de Lorenzo y Gutierez, Jr. Jüan (Martin) Martinez',/**/
        ],
        ['Mr.', 'Jüan', 'Martinez', 'de Lorenzo y Gutierez', 'Martin', 'Jr.', []],
      ],

        // Errors tests.
        // Garbage test. Implemented.
      [
        [
          'as;dfkj ;aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg oaweifugy',
        ],
        [
          '',
          'as;dfkj',
          ';aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg',
          'oaweifugy',
          '',
          '',
          ['Warning: 19 middle names'],
        ],
      ],/**/
        // No input test fixed.
      [
        [
          null,
        ],
        ['', '', '', '', '', '', ['Incorrect input to parse.']],
      ],/**/
    ];

    /**
     * Lists of names.
     * Test case for name part parsing.
     *
     * @var array
     */
    const PART_NAMES = [
        // Return one part of name implemented.
      [
        'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
        'Mr.',
        'title',
      ],
      [
        'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
        'Jüan',
        'first',
      ],
      [
        'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
        'Martinez',
        'middle',
      ],
      [
        'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
        'de Lorenzo y Gutierez',
        'last',
      ],
      [
        'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
        'Martin',
        'nick',
      ],
      [
        'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
        'Jr.',
        'suffix',
      ],
        // Additional tests on error part.
      [
        null,
        'Incorrect input to parse.',
        'error',
      ],
      [
        'Jüan, Martinez, de Lorenzo y Gutierez',
        'Can\'t flip around multiple \',\' characters in name string \'Jüan, Martinez, de Lorenzo y Gutierez\'.',
        'error',
      ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    /**
     * Test FullNameParser parse.
     *
     * @coversDefaultClass
     */
    public function testNameList()
    {
        $options = [
          'throws' => false,
        ];
        $parser = new Parser($options);
        foreach (self::NAMES as $nameArr) {
            foreach ($nameArr[0] as $name) {
                $nameObject = $parser->parse($name);
                $error_msg = sprintf(self::OUTPUT_STR, "academic title", $nameArr[1][0], $name);
                $this->assertEquals($nameArr[1][0], $nameObject->getAcademicTitle(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "nickname", $nameArr[1][4], $name);
                $this->assertEquals($nameArr[1][4], $nameObject->getNickNames(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "suffix", $nameArr[1][5], $name);
                $this->assertEquals($nameArr[1][5], $nameObject->getSuffix(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "last name", $nameArr[1][3], $name);
                $this->assertEquals($nameArr[1][3], $nameObject->getLastName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "first name", $nameArr[1][1], $name);
                $this->assertEquals($nameArr[1][1], $nameObject->getFirstName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "middle name", $nameArr[1][2], $name);
                $this->assertEquals($nameArr[1][2], $nameObject->getMiddleName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "errors", implode(', ', $nameArr[1][6]), $name);
                $this->assertEquals($nameArr[1][6], $nameObject->getErrors(), $error_msg);
            }
        }
    }

    /**
     * Test throw error by default.
     *
     * @expectedException \ADCI\FullNameParser\Exception\IncorrectInputException
     * @covers \ADCI\FullNameParser\Exception\IncorrectInputException
     */
    public function testThrows()
    {
        $this->parser->parse(null);
    }

    /**
     * Test not throw error by set options.
     *
     * @coversNothing
     */
    public function testDoesNotThrow()
    {
        $options = [
          'throws' => false,
        ];
        $parser = new Parser($options);
        $nameObject = $parser->parse(null);
        $this->assertInstanceOf(Name::class, $nameObject);
    }

    /**
     * Test fix case parse.
     *
     * @coversDefaultClass
     */
    public function testCaseNameList()
    {
        foreach (self::CASE_NAMES as $nameArr) {
            foreach ($nameArr[0] as $name) {
                $fixCase = isset($nameArr[2]) ? $nameArr[2] : true;
                $options = [
                  'throws' => false,
                  'fix_case' => $fixCase,
                ];
                $parser = new Parser($options);
                $nameObject = $parser->parse($name);
                if ($nameObject instanceof Name) {
                    $error_msg = sprintf(self::OUTPUT_STR, "academic title", $nameArr[1][0], $name);
                    $this->assertEquals($nameArr[1][0], $nameObject->getAcademicTitle(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "nickname", $nameArr[1][4], $name);
                    $this->assertEquals($nameArr[1][4], $nameObject->getNickNames(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "suffix", $nameArr[1][5], $name);
                    $this->assertEquals($nameArr[1][5], $nameObject->getSuffix(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "last name", $nameArr[1][3], $name);
                    $this->assertEquals($nameArr[1][3], $nameObject->getLastName(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "first name", $nameArr[1][1], $name);
                    $this->assertEquals($nameArr[1][1], $nameObject->getFirstName(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "middle name", $nameArr[1][2], $name);
                    $this->assertEquals($nameArr[1][2], $nameObject->getMiddleName(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "errors", implode(', ', $nameArr[1][6]), $name);
                    $this->assertEquals($nameArr[1][6], $nameObject->getErrors(), $error_msg);
                } else {
                    self::fail(sprintf('Incorrect object type in name %s', $name));
                }
            }
        }
    }

    /**
     * Test part of names parse.
     *
     * @coversDefaultClass
     */
    public function testPartNameList()
    {
        foreach (self::PART_NAMES as $nameArr) {
            $part = isset($nameArr[2]) ? $nameArr[2] : 'all';
            $options = [
              'throws' => false,
              'part' => $part,
            ];
            $parser = new Parser($options);
            $nameObject = $parser->parse($nameArr[0]);
            if (is_string($nameObject) || ($part === 'error' && is_array($nameObject))) {
                $error_msg = sprintf(self::OUTPUT_STR, "part", $nameArr[1], $nameArr[0]);
                $this->assertEquals($nameArr[1], ((array)$nameObject)[0], $error_msg);
            } else {
                self::fail(sprintf('Incorrect object type %s in name %s', $part, $nameArr[0]));
            }
        }
    }

    /**
     * Not any assertion because not so easy to assert warning.
     * Simple coverage.
     *
     * @coversDefaultClass
     */
    public function testManyMiddleNames()
    {
        $name = [
          [
            'as;dfkj ;aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg oaweifugy',
          ],
          [
            '',
            'as;dfkj',
            ';aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg',
            'oaweifugy',
            '',
            '',
            ['Warning: 19 middle names'],
          ],
        ];
        try {
            $this->parser->parse($name[0][0]);
        } catch (PHPUnit_Framework_Error_Warning $ex) {
        }
    }

    /**
     * Not any assertion because not so easy to assert warning.
     * Simple coverage.
     *
     * @covers \ADCI\FullNameParser\Exception\ManyMiddleNamesException
     */
    public function testManyMiddleNamesEx()
    {
        $name = [
          [
            'as;dfkj ;aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg oaweifugy',
          ],
          [
            '',
            'as;dfkj',
            ';aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg',
            'oaweifugy',
            '',
            '',
            ['Warning: 19 middle names'],
          ],
        ];
        try {
            $this->parser->parse($name[0][0]);
        } catch (PHPUnit_Framework_Error_Warning $ex) {
        }
    }

    /**
     * Additional test for exception.
     *
     * @expectedException \ADCI\FullNameParser\Exception\IncorrectInputException
     * @coversDefaultClass
     */
    public function testThrowsEx()
    {
        $this->parser->parse(null);
    }

    /**
     * @expectedException \ADCI\FullNameParser\Exception\LastNameNotFoundException
     * @coversDefaultClass
     */
    public function testNoLastNameDefaultException()
    {
        $name = 'Edward';
        $this->parser->parse($name);
    }

    /**
     * Additional test for exception.
     *
     * @expectedException \ADCI\FullNameParser\Exception\FirstNameNotFoundException
     * @coversDefaultClass
     */
    public function testNoFirstNameDefaultException()
    {
        $name = 'Mr. Hyde';
        $this->parser->parse($name);
    }
}
