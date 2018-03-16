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
        // Switch to normal case by default in this case test. Parser not switch case by default.
        [
            'original' => [
                'MR. JÜAN MARTINEZ (MARTIN) DE LORENZO Y GUTIEREZ JR.',
                'mr. jüan martinez (martin) de lorenzo y gutierez jr.',
            ],
            'title' => 'Mr.',
            'first' => 'Jüan',
            'middle' => 'Martinez',
            'last' => 'de Lorenzo y Gutierez',
            'nick' => 'Martin',
            'suffix' => 'Jr.',
            'errors' => [],
        ],
        // Checking for not switch case if not need.
        // Switch option must be set false.
        [
            'original' => 'Mr. JÜAN MARTINEZ (MARTIN) DE LORENZO Y GUTIEREZ Jr.',
            'title' => 'Mr.',
            'first' => 'JÜAN',
            'middle' => 'MARTINEZ',
            'last' => 'DE LORENZO Y GUTIEREZ',
            'nick' => 'MARTIN',
            'suffix' => 'Jr.',
            'errors' => [],
            'fixCase' => false,
        ],
        // Checking for switch case if need.
        // Switch option must be set true.
        [
            'original' => 'Mr. JÜAN MARTINEZ (MARTIN) DE LORENZO Y GUTIEREZ JR.',
            'title' => 'Mr.',
            'first' => 'Jüan',
            'middle' => 'Martinez',
            'last' => 'de Lorenzo y Gutierez',
            'nick' => 'Martin',
            'suffix' => 'Jr.',
            'errors' => [],
            'fixCase' => true,
        ],
        // Checking for not switch case if not need.
        // Switch option must be set false.
        [
            'original' => 'mr. jüan martinez (martin) de lorenzo y gutierez jr.',
            'title' => 'mr.',
            'first' => 'jüan',
            'middle' => 'martinez',
            'last' => 'de lorenzo y gutierez',
            'nick' => 'martin',
            'suffix' => 'jr.',
            'errors' => [],
            'fixCase' => false,
        ],
    ];

    /**
     * Lists of names.
     * Test case for complex parsing.
     *
     * @var array
     */
    const NAMES = [
        [
            'original' => ['David Davis', 'Davis, David'],
            'title' => '',
            'first' => 'David',
            'middle' => '',
            'last' => 'Davis',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => ['Gerald Böck', 'Böck, Gerald'],
            'title' => '',
            'first' => 'Gerald',
            'middle' => '',
            'last' => 'Böck',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => ['David William Davis', 'Davis, David William'],
            'title' => '',
            'first' => 'David',
            'middle' => 'William',
            'last' => 'Davis',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => ['Vincent Van Gogh', 'Van Gogh, Vincent'],
            'title' => '',
            'first' => 'Vincent',
            'middle' => '',
            'last' => 'Van Gogh',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => ['Lorenzo de Médici', 'de Médici, Lorenzo'],
            'title' => '',
            'first' => 'Lorenzo',
            'middle' => '',
            'last' => 'de Médici',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => ['Jüan de la Véña', 'de la Véña, Jüan'],
            'title' => '',
            'first' => 'Jüan',
            'middle' => '',
            'last' => 'de la Véña',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => [
                'Jüan Martinez de Lorenzo y Gutierez',
                'de Lorenzo y Gutierez, Jüan Martinez',
            ],
            'title' => '',
            'first' => 'Jüan',
            'middle' => 'Martinez',
            'last' => 'de Lorenzo y Gutierez',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => [
                'Orenthal James "O. J." Simpson',
                'Orenthal \'O. J.\' James Simpson',
                '(O. J.) Orenthal James Simpson',
                'Simpson, Orenthal James "O. J."',
                'Simpson, Orenthal ‘O. J.’ James',
                'Simpson, [O. J.] Orenthal James',
            ],
            'title' => '',
            'first' => 'Orenthal',
            'middle' => 'James',
            'last' => 'Simpson',
            'nick' => 'O. J.',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => ['Sammy Davis, Jr.', 'Davis, Sammy, Jr.'],
            'title' => '',
            'first' => 'Sammy',
            'middle' => '',
            'last' => 'Davis',
            'nick' => '',
            'suffix' => 'Jr.',
            'errors' => [],
        ],
        // Multiple suffix.
        [
            'original' => [
                'John P. Doe-Ray, Jr., CLU, CFP, LUTC',
                'Doe-Ray, John P., Jr., CLU, CFP, LUTC',
                'John P. Doe-Ray Jr., CLU, CFP, LUTC',
                'Doe-Ray, John P. Jr., CLU, CFP, LUTC',
            ],
            'title' => '',
            'first' => 'John',
            'middle' => 'P.',
            'last' => 'Doe-Ray',
            'nick' => '',
            'suffix' => 'Jr., CLU, CFP, LUTC',
            'errors' => [],
        ],
        [
            'original' => [
                'Dr. John P. Doe-Ray, Jr.',
                'Dr. Doe-Ray, John P., Jr.',
                'Doe-Ray, Dr. John P., Jr.',
            ],
            'title' => 'Dr.',
            'first' => 'John',
            'middle' => 'P.',
            'last' => 'Doe-Ray',
            'nick' => '',
            'suffix' => 'Jr.',
            'errors' => [],
        ],
        [
            'original' => [
                'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
                'de Lorenzo y Gutierez, Mr. Jüan Martinez (Martin) Jr.',
                'de Lorenzo y Gutierez, Mr. Jüan (Martin) Martinez Jr.',
                'Mr. de Lorenzo y Gutierez, Jüan Martinez (Martin) Jr.',
                'Mr. de Lorenzo y Gutierez, Jüan (Martin) Martinez Jr.',
                'Mr. de Lorenzo y Gutierez Jr., Jüan Martinez (Martin)',
                'Mr. de Lorenzo y Gutierez Jr., Jüan (Martin) Martinez',
                'Mr. de Lorenzo y Gutierez, Jr. Jüan Martinez (Martin)',
                'Mr. de Lorenzo y Gutierez, Jr. Jüan (Martin) Martinez',
            ],
            'title' => 'Mr.',
            'first' => 'Jüan',
            'middle' => 'Martinez',
            'last' => 'de Lorenzo y Gutierez',
            'nick' => 'Martin',
            'suffix' => 'Jr.',
            'errors' => [],
        ],
        // Errors checking.
        // Garbage input.
        [
            'original' => 'as;dfkj ;aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg oaweifugy',
            'title' => '',
            'first' => 'as;dfkj',
            'middle' => ';aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg',
            'last' => 'oaweifugy',
            'nick' => '',
            'suffix' => '',
            'errors' => ['Warning: 19 middle names'],
        ],
        // Empty input.
        [
            'original' => null,
            'title' => '',
            'first' => '',
            'middle' => '',
            'last' => '',
            'nick' => '',
            'suffix' => '',
            'errors' => ['Incorrect input to parse.'],
        ],
        /* @see https://github.com/ADCI/full-name-parser/issues/1 */
        [
            'original' => 'John J Oliveri',
            'title' => '',
            'first' => 'John',
            'middle' => 'J',
            'last' => 'Oliveri',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        /* @see https://github.com/ADCI/full-name-parser/issues/2 */
        [
            'original' => 'Villuendas, M. V.',
            'title' => '',
            'first' => 'M.',
            'middle' => 'V.',
            'last' => 'Villuendas',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        /* @see https://github.com/ADCI/full-name-parser/issues/6 */
        [
            'original' => 'Jokubas Phillip Gardner ',
            'title' => '',
            'first' => 'Jokubas',
            'middle' => 'Phillip',
            'last' => 'Gardner',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
    ];

    // List from https://github.com/mklaber/node-another-name-parser
    // Except not valid strings.
    const ADDITIONAL_NAMES = [
        [
            'original' => 'Doe, John',
            'title' => '',
            'first' => 'John',
            'middle' => '',
            'last' => 'Doe',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Doe, John P',
            'title' => '',
            'first' => 'John',
            'middle' => 'P',
            'last' => 'Doe',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Doe, Dr. John P',
            'title' => 'Dr.',
            'first' => 'John',
            'middle' => 'P',
            'last' => 'Doe',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'John R Doe-Smith',
            'title' => '',
            'first' => 'John',
            'middle' => 'R',
            'last' => 'Doe-Smith',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'John Doe',
            'title' => '',
            'first' => 'John',
            'middle' => '',
            'last' => 'Doe',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Mr. Anthony R Von Fange III',
            'title' => 'Mr.',
            'first' => 'Anthony',
            'middle' => 'R',
            'last' => 'Von Fange',
            'nick' => '',
            'suffix' => 'III',
            'errors' => [],
        ],
        [
            'original' => 'Sara Ann Fraser',
            'title' => '',
            'first' => 'Sara',
            'middle' => 'Ann',
            'last' => 'Fraser',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        /* Compound first names.
        Not implemented.
        [
            'original' => 'Mary Ann Fraser',
            'title' => '',
            'first' => 'Mary Ann',
            'middle' => '',
            'last' => 'Fraser',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Fraser, Mary Ann',
            'title' => '',
            'first' => 'Mary Ann',
            'middle' => '',
            'last' => 'Fraser',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Jo Ellen Mary St. Louis',
            'title' => '',
            'first' => 'Jo Ellen',
            'middle' => 'Mary',
            'last' => 'St. Louis',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],*/
        [
            'original' => 'Adam',
            'title' => '',
            'first' => 'Adam',
            'middle' => '',
            'last' => '',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Donald "Don" Rex St. Louis',
            'title' => '',
            'first' => 'Donald',
            'middle' => 'Rex',
            'last' => 'St. Louis',
            'nick' => 'Don',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Donald (Don) Rex St. Louis',
            'title' => '',
            'first' => 'Donald',
            'middle' => 'Rex',
            'last' => 'St. Louis',
            'nick' => 'Don',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Mary Ann',
            'title' => '',
            'first' => 'Mary',
            'middle' => '',
            'last' => 'Ann',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Jonathan Smith',
            'title' => '',
            'first' => 'Jonathan',
            'middle' => '',
            'last' => 'Smith',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Anthony Von Fange III',
            'title' => '',
            'first' => 'Anthony',
            'middle' => '',
            'last' => 'Von Fange',
            'nick' => '',
            'suffix' => 'III',
            'errors' => [],
        ],
        [
            'original' => 'Mr John Doe',
            'title' => 'Mr',
            'first' => 'John',
            'middle' => '',
            'last' => 'Doe',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Mr John Doe PhD, Esq',
            'title' => 'Mr',
            'first' => 'John',
            'middle' => '',
            'last' => 'Doe',
            'nick' => '',
            'suffix' => 'PhD, Esq',
            'errors' => [],
        ],
        [
            'original' => 'Mrs. Jane Doe',
            'title' => 'Mrs.',
            'first' => 'Jane',
            'middle' => '',
            'last' => 'Doe',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => ['Smarty Pants PhD', 'Smarty Pants, PhD'],
            'title' => '',
            'first' => 'Smarty',
            'middle' => '',
            'last' => 'Pants',
            'nick' => '',
            'suffix' => 'PhD',
            'errors' => [],
        ],
        [
            'original' => 'Mark P Williams',
            'title' => '',
            'first' => 'Mark',
            'middle' => 'P',
            'last' => 'Williams',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Aaron bin Omar',
            'title' => '',
            'first' => 'Aaron',
            'middle' => '',
            'last' => 'bin Omar',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Richard van der Dys',
            'title' => '',
            'first' => 'Richard',
            'middle' => '',
            'last' => 'van der Dys',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'Joe de la Cruz',
            'title' => '',
            'first' => 'Joe',
            'middle' => '',
            'last' => 'de la Cruz',
            'nick' => '',
            'suffix' => '',
            'errors' => [],
        ],
        [
            'original' => 'John Doe Esquire',
            'title' => '',
            'first' => 'John',
            'middle' => '',
            'last' => 'Doe',
            'nick' => '',
            'suffix' => 'Esquire',
            'errors' => [],
        ],
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
            'original' => 'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
            'title' => 'Mr.',
        ],
        [
            'original' => 'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
            'first' => 'Jüan',
        ],
        [
            'original' => 'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
            'middle' => 'Martinez',
        ],
        [
            'original' => 'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
            'last' => 'de Lorenzo y Gutierez',
        ],
        [
            'original' => 'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
            'nick' => 'Martin',
        ],
        [
            'original' => 'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.',
            'suffix' => 'Jr.',
        ],
        // Additional tests on error part.
        [
            'original' => null,
            'error' => 'Incorrect input to parse.',
        ],
        [
            'original' => 'Jüan, Martinez, de Lorenzo y Gutierez',
            'error' => "Can't flip around multiple ',' characters in name string 'Jüan, Martinez, de Lorenzo y Gutierez'.",
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
    public function testAdditionalNameList()
    {
        $options = [
            'throws' => false,
        ];
        $parser = new Parser($options);
        foreach (self::ADDITIONAL_NAMES as $nameArr) {
            foreach ((array)$nameArr['original'] as $name) {
                $nameObject = $parser->parse($name);
                $error_msg = sprintf(self::OUTPUT_STR, "academic title", $nameArr['title'], $name);
                $this->assertEquals($nameArr['title'], $nameObject->getAcademicTitle(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "nickname", $nameArr['nick'], $name);
                $this->assertEquals($nameArr['nick'], $nameObject->getNickNames(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "suffix", $nameArr['suffix'], $name);
                $this->assertEquals($nameArr['suffix'], $nameObject->getSuffix(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "last name", $nameArr['last'], $name);
                $this->assertEquals($nameArr['last'], $nameObject->getLastName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "first name", $nameArr['first'], $name);
                $this->assertEquals($nameArr['first'], $nameObject->getFirstName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "middle name", $nameArr['middle'], $name);
                $this->assertEquals($nameArr['middle'], $nameObject->getMiddleName(), $error_msg);
            }
        }
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
            foreach ((array)$nameArr['original'] as $name) {
                $nameObject = $parser->parse($name);
                $error_msg = sprintf(self::OUTPUT_STR, "academic title", $nameArr['title'], $name);
                $this->assertEquals($nameArr['title'], $nameObject->getAcademicTitle(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "nickname", $nameArr['nick'], $name);
                $this->assertEquals($nameArr['nick'], $nameObject->getNickNames(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "suffix", $nameArr['suffix'], $name);
                $this->assertEquals($nameArr['suffix'], $nameObject->getSuffix(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "last name", $nameArr['last'], $name);
                $this->assertEquals($nameArr['last'], $nameObject->getLastName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "first name", $nameArr['first'], $name);
                $this->assertEquals($nameArr['first'], $nameObject->getFirstName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "middle name", $nameArr['middle'], $name);
                $this->assertEquals($nameArr['middle'], $nameObject->getMiddleName(), $error_msg);
                $error_msg = sprintf(self::OUTPUT_STR, "errors", implode(', ', $nameArr['errors']), $name);
                $this->assertEquals($nameArr['errors'], $nameObject->getErrors(), $error_msg);
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
            foreach ((array)$nameArr['original'] as $name) {
                $fixCase = isset($nameArr['fixCase']) ? $nameArr['fixCase'] : true;
                $options = [
                    'throws' => false,
                    'fix_case' => $fixCase,
                ];
                $parser = new Parser($options);
                $nameObject = $parser->parse($name);
                if ($nameObject instanceof Name) {
                    $error_msg = sprintf(self::OUTPUT_STR, "academic title", $nameArr['title'], $name);
                    $this->assertEquals($nameArr['title'], $nameObject->getAcademicTitle(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "nickname", $nameArr['nick'], $name);
                    $this->assertEquals($nameArr['nick'], $nameObject->getNickNames(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "suffix", $nameArr['suffix'], $name);
                    $this->assertEquals($nameArr['suffix'], $nameObject->getSuffix(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "last name", $nameArr['last'], $name);
                    $this->assertEquals($nameArr['last'], $nameObject->getLastName(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "first name", $nameArr['first'], $name);
                    $this->assertEquals($nameArr['first'], $nameObject->getFirstName(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "middle name", $nameArr['middle'], $name);
                    $this->assertEquals($nameArr['middle'], $nameObject->getMiddleName(), $error_msg);
                    $error_msg = sprintf(self::OUTPUT_STR, "errors", implode(', ', $nameArr['errors']), $name);
                    $this->assertEquals($nameArr['errors'], $nameObject->getErrors(), $error_msg);
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
            foreach ($nameArr as $key => $value) {
                switch ($key) {
                    case 'original':
                        $originalName = $value;
                        break;
                    default:
                        $part = isset($key) ? $key : 'all';
                        $namePart = $value;
                        $options = [
                            'throws' => false,
                            'part' => $part,
                        ];
                        break;
                }
            }
            $parser = new Parser($options);
            $nameObject = $parser->parse($originalName);
            if (is_string($nameObject) || ($part === 'error' && is_array($nameObject))) {
                $error_msg = sprintf(self::OUTPUT_STR, "part", $namePart, $originalName);
                $this->assertEquals($namePart, ((array)$nameObject)[0], $error_msg);
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
            'original' => 'as;dfkj ;aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg oaweifugy',
            'title' => '',
            'first' => 'as;dfkj',
            'middle' => ';aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg',
            'last' => 'oaweifugy',
            'nick' => '',
            'suffix' => '',
            'errors' => ['Warning: 19 middle names'],
        ];
        try {
            $this->parser->parse($name['original']);
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
            'original' => 'as;dfkj ;aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg oaweifugy',
            'title' => '',
            'first' => 'as;dfkj',
            'middle' => ';aerha;sfa ef;oia;woeig hz;sofi hz;oifj;zoseifj zs;eofij z;soeif jzs;oefi jz;osif z;osefij zs;oif jz;soefihz;sodifh z;sofu hzsieufh zlsiudfh zksefiulzseofih ;zosufh ;oseihgfz;osef h:OSfih lziusefhaowieufyg',
            'last' => 'oaweifugy',
            'nick' => '',
            'suffix' => '',
            'errors' => ['Warning: 19 middle names'],
        ];
        try {
            $this->parser->parse($name['original']);
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
