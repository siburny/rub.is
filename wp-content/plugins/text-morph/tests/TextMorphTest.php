<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TextMorphTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once(dirname(__FILE__) . '/../../../../wp-load.php');
    }

    public function shortcode_display_minutes_provider()
    {
        return [
            [
                '[morph time="180" display="minutes"]',
                '3 hours'
            ],
            [
                '[morph time="100" display="minutes"]',
                '1 hour 40 minutes'
            ],
            [
                '[morph number="100" display="minutes"]',
                '1 hour 40 minutes'
            ],
            [
                '[morph time="60" display="minutes"]',
                '1 hour'
            ],
            [
                '[morph number="50" display="minutes"]',
                '50 minutes'
            ]
        ];
    }


    public function shortcode_display_foot_provider()
    {
        return [
            [
                '[morph length="72" display="foot"]',
                '6 feet'
            ],
            [
                '[morph length="0" display="foot"]',
                '0 inches'
            ],
            [
                '[morph length="1" display="foot"]',
                '1 inch'
            ],
            [
                '[morph length="2" display="foot"]',
                '2 inches'
            ],
            [
                '[morph length="77" display="foot"]',
                '6 feet 5 inches'
            ]
        ];
    }

    public function shortcode_display_expire()
    {
        return [
            [
                '[morph data="is|was" expire="6/1/2020"]',
                'was'
            ],
            [
                '[morph data="is|was" expire="6/1/2025"]',
                'is'
            ],
            [
                '[morph data="not released|released" expire="6/1/2020"]',
                'released'
            ],
            [
                '[morph data="not released|released" expire="6/1/2020"]',
                'released'
            ],
            [
                '[morph data="takes|" expire="6/1/2020"]',
                ''
            ],
            [
                '[morph data="takes|" expire="6/1/2025"]',
                'takes'
            ],
            [
                '[morph data="|released" expire="6/1/2025"]',
                ''
            ],
            [
                '[morph data="|released" expire="6/1/2020"]',
                'released'
            ],
        ];
    }

    public function shortcode_display_icons()
    {
        return [
            [
                '[morph data="male" icon="true"]',
                '♂️'
            ],
            [
                '[morph data="female" icon="true"]',
                '♀️'
            ],
        ];
    }
    
    public function shortcode_display_various_fixes()
    {
        return [
            [
                '[morph number="434433" display="abbr"]',
                '434 thousand'
            ],
            [
                '[morph text="algebra" description="true"]',
                'Algebra is algebra.'
            ],
            [
                '[morph text="some text here" description="true"]',
                ''
            ],
        ];
    }

    /**
     * @dataProvider shortcode_display_minutes_provider
     * @dataProvider shortcode_display_foot_provider
     * @dataProvider shortcode_display_expire
     * @dataProvider shortcode_display_various_fixes
     * @dataProvider shortcode_display_icons
     */
    public function test_shortcode_should_output($shortcode, $output_expected)
    {
        $output = do_shortcode($shortcode);
        $this->assertEquals($output_expected, $output);
    }
}
