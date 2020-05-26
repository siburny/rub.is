<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class textmorphtest extends TestCase
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

    /**
     * @dataProvider shortcode_display_minutes_provider
     * @dataProvider shortcode_display_foot_provider
     */
    public function test_shortcode_should_output($shortcode, $output_expected)
    {
        $output = do_shortcode($shortcode);
        $this->assertEquals($output_expected, $output);
    }
}
