<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class datecalctest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once(dirname(__FILE__) . '/../../../../wp-load.php');
    }

    public function shortcode_seasons_should_output_provider()
    {
        return [
            [
                "[datecalc date=\"2/1/2020\" display=\"s\" ]",
                "Winter"
            ],
            [
                "[datecalc date=\"2/1/2020\" display=\"s yyyy\" ]",
                "Winter 2020"
            ],
            [
                "[datecalc date=\"1/5/2020\" display=\"s\" count=\"true\"]",
                "36 day of Winter"
            ],
            [
                "[datecalc date=\"2/5/2020\" display=\"s\" count=\"true\"]",
                "67 day of Winter"
            ],
            [
                "[datecalc date=\"3/5/2020\" display=\"s\" count=\"true\"]",
                "5 day of Spring"
            ],
            [
                "[datecalc date=\"11/5/2020\" display=\"s\" count=\"true\"]",
                "66 day of Fall (autumn)"
            ],
            [
                "[datecalc date=\"12/5/2020\" display=\"s\" count=\"true\"]",
                "5 day of Winter"
            ],
            [
                "[datecalc date=\"2/1/2020\" display=\"s\" count=\"true\" ordinalize=\"true\"]",
                "63rd day of Winter"
            ],
            [
                "[datecalc date=\"2/1/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "29 days left till Spring"
            ],
            [
                "[datecalc date=\"2/15/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "15 days left till Spring"
            ],
            [
                "[datecalc date=\"2/29/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "1 day left till Spring"
            ],
            [
                "[datecalc date=\"10/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "52 days left till Winter"
            ],
            [
                "[datecalc date=\"11/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "21 days left till Winter"
            ],
            [
                "[datecalc date=\"12/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "81 days left till Spring"
            ],
            [
                "[datecalc date=\"1/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "51 days left till Spring"
            ],
            [
                "[datecalc date=\"2/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "20 days left till Spring"
            ],
            [
                "[datecalc date=\"3/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "83 days left till Summer"
            ],
        ];
    }

    public function shortcode_elements_should_output_provider()
    {
        return [
            [
                '[datecalc date="4/10/1984" element="true"]',
                'Fire'
            ],
            [
                '[datecalc date="5/10/1984" element="true"]',
                'Earth'
            ],
            [
                '[datecalc date="6/10/1984" element="true"]',
                'Air'
            ],
            [
                '[datecalc date="7/10/1984" element="true"]',
                'Water'
            ],
        ];
    }

    public function shortcode_elements_should_output_description_provider()
    {
        return [
            [
                '[datecalc date="4/10/1984" element="true" description="true"]',
                'Fire description'
            ],
            [
                '[datecalc date="5/10/1984" element="true" description="true"]',
                'Earth description'
            ],
            [
                '[datecalc date="6/10/1984" element="true" description="true"]',
                'Air description'
            ],
            [
                '[datecalc date="7/10/1984" element="true" description="true"]',
                'Water description'
            ],
        ];
    }

    public function shortcode_quality_should_output_provider()
    {
        return [
            [
                '[datecalc date="4/10/1984" quality="true"]',
                'Cardinal'
            ],
            [
                '[datecalc date="5/10/1984" quality="true"]',
                'Fixed'
            ],
            [
                '[datecalc date="6/10/1984" quality="true"]',
                'Mutable'
            ],
        ];
    }

    public function shortcode_quality_should_output_description_provider()
    {
        return [
            [
                '[datecalc date="4/10/1984" quality="true" description="true"]',
                'Cardinal description'
            ],
            [
                '[datecalc date="5/10/1984" quality="true" description="true"]',
                'Fixed description'
            ],
            [
                '[datecalc date="6/10/1984" quality="true" description="true"]',
                'Mutable description'
            ],
        ];
    }

    /**
     * @dataProvider shortcode_seasons_should_output_provider
     * @dataProvider shortcode_elements_should_output_provider
     * @dataProvider shortcode_elements_should_output_description_provider
     * @dataProvider shortcode_quality_should_output_provider
     * @dataProvider shortcode_quality_should_output_description_provider
     */
    public function test_shortcode_should_output($shortcode, $output_expected)
    {
        $output = do_shortcode($shortcode);
        $this->assertEquals($output_expected, $output);
    }
}
