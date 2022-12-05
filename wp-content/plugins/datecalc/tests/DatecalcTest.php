<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DatecalcTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once(dirname(__FILE__) . '/../../../../wp-load.php');
    }

    public function shortcode_should_output_provider()
    {
        return [

            // Season
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
                "29 days left until Spring"
            ],
            [
                "[datecalc date=\"2/15/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "15 days left until Spring"
            ],
            [
                "[datecalc date=\"2/29/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "1 day left until Spring"
            ],
            [
                "[datecalc date=\"10/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "52 days left until Winter"
            ],
            [
                "[datecalc date=\"11/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "21 days left until Winter"
            ],
            [
                "[datecalc date=\"12/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "81 days left until Spring"
            ],
            [
                "[datecalc date=\"1/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "51 days left until Spring"
            ],
            [
                "[datecalc date=\"2/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "20 days left until Spring"
            ],
            [
                "[datecalc date=\"3/10/2020\" display=\"s\" count=\"true\" difference=\"true\"]",
                "83 days left until Summer"
            ],


            // Element
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
            [
                '[datecalc date="4/10/1982" element="true" icon="true"]',
                '🔥'
            ],
            [
                '[datecalc date="5/10/1984" element="true" icon="true"]',
                '🌎'
            ],
            [
                '[datecalc date="6/10/1984" element="true" icon="true"]',
                '💨'
            ],
            [
                '[datecalc date="7/10/1984" element="true" icon="true"]',
                '🌊'
            ],
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


            // Quality
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

            // Week number
            [
                '[datecalc date="12/25/1994" display="w"]',
                '52'
            ],
            [
                '[datecalc date="12/25/1994" display="w" ordinalize="true"]',
                '52nd'
            ],


            // Holidays
            [
                '[datecalc date="12/25/2020" holiday="true"]',
                'Christmas Day<br />Grav Mass Day'
            ],
            [
                '[datecalc date="12/25/2020" holiday="true" separator="<li>"]',
                '<ul><li>Christmas Day<li>Grav Mass Day</ul>'
            ],
            [
                '[datecalc date="01/07/2020" holiday="true"]',
                'Orthodox Christmas'
            ],
            [
                '[datecalc date="12/25/2020" holiday="true" filter="country|US"]',
                'Christmas Day'
            ],
            [
                '[datecalc date="12/25/2020" holiday="true" filter="country|RU"]',
                'No major holidays found for this date.'
            ],
            [
                '[datecalc date="01/07/2020" holiday="true" filter="country|US"]',
                'No major holidays found for this date.'
            ],
            [
                '[datecalc date="01/07/2020" holiday="true" filter="country|RU"]',
                'Orthodox Christmas'
            ],
            [
                '[datecalc date="01/07/2020" holiday="true" filter="type|XX"]',
                'No major holidays found for this date.'
            ],
            [
                '[datecalc date="12/25/1984" holidays="true" filter="type|major"]',
                'Christmas Day'
            ],
            [
                '[datecalc date="1/1/2020" holidays="true" filter="type|christian"]',
                'Solemnity of Mary'
            ],
            [
                '[datecalc date="1/1/1984" holidays="true" filter="type|major" data="description"]',
                'This is the descrition for NY\'s Day'
            ],


            // Roman dates
            [
                '[datecalc date="5/7/2020" roman="true"]',
                'VVIIMMXX'
            ],
            [
                '[datecalc date="5/7/2020" roman="true" display="m d yyyy"]',
                'V VII MMXX'
            ],


            // Quarter
            [
                '[datecalc date="1/1/2020" display="q"]',
                '1st quarter'
            ],
            [
                '[datecalc date="1/1/2020" display="q text"]',
                'first quarter'
            ],
            [
                '[datecalc date="8/1/2020" display="q abbr"]',
                'Q3'
            ],


            // Chinese zodiac
            [
                '[datecalc date="6/2/1981" chinesezodiac="true"]',
                'Rooster'
            ],
            [
                '[datecalc date="1/1/2020" chinesezodiac="true"]',
                'Rat'
            ],
            [
                '[datecalc date="6/2/1981" chinesezodiac="true" icon="true"]',
                '🐓'
            ],
            [
                '[datecalc date="1/1/2020" chinesezodiac="true" icon="true"]',
                '🐀'
            ],
            [
                '[datecalc date="6/2/1981" chinesezodiac="true" description="true"]',
                'Rooster Description Line 1<br/><br/>Rooster Description Line 2<br/><br/>'
            ],


            // Planet
            [
                '[datecalc date="6/2/1981" planet="true"]',
                'Mercury'
            ],
            [
                '[datecalc date="1/1/2020" planet="true"]',
                'Saturn'
            ],
            [
                '[datecalc date="6/2/1981" planet="true" icon="true"]',
                '☿'
            ],
            [
                '[datecalc date="1/1/2020" planet="true" icon="true"]',
                '♄'
            ],

            // Moonphase
            [
                '[datecalc date="1/1/2020" moonphase="true"]',
                'Waxing'
            ],
            [
                '[datecalc date="1/1/2022" moonphase="true"]',
                'Waning Crescent'
            ],
            [
                '[datecalc date="1/1/2020" moonphase="true" icon="true"]',
                '🌒'
            ],
            [
                '[datecalc date="1/1/2022" moonphase="true" icon="true"]',
                '🌘'
            ],



            // Population
            [
                '[datecalc date="6/2/1981" population="true"]',
                '4,536,996,762'
            ],
            [
                '[datecalc date="6/2/1981" population="true" display="abbr"]',
                '4.54 billion'
            ],


            // Game / Movie / Song
            [
                '[datecalc date="6/2/2010" game="true"]',
                'Mass Effect 2 developed by BioWare.'
            ],
            [
                '[datecalc date="6/2/1981" movies="true"]',
                'Raiders of the Lost Ark directed by Steven Spielberg starring Harrison Ford, Karen Allen, Paul Freeman.'
            ],
            [
                '[datecalc date="6/2/1981" song="true"]',
                '&quot;Bette Davis Eyes&quot; by Kim Carnes'
            ],


            // Baby name
            [
                '[datecalc date="6/2/1981" babynames="true" display="girl"]',
                'Jennifer'
            ],
            [
                '[datecalc date="6/2/1981" babynames="true" display="girl" numbers="true"]',
                '57,049'
            ],
            [
                '[datecalc date="6/2/2000" babynames="true" display="boy"]',
                'Jacob'
            ],
            [
                '[datecalc date="6/2/2000" babynames="true" display="boy" numbers="true"]',
                '34,471'
            ],


            // Birthday
            [
                '[datecalc date="6/1/1981" birthdays="true"]',
                'You share a birthday with Amy Schumer (comedian)'
            ],
            [
                '[datecalc date="2/3/1982" birthdays="true"]',
                'You share a birthday with Bridget Regan (tv actress), Chris Thompson (YouTuber)'
            ],


            // Event
            [
                '[datecalc date="1/20/1981" event="true"]',
                '<p><b>2009</b> &ndash; Barack Obama, inaugurated as the 44th President of the United States of America, becomes the United States\' first African-American president.</p><p><b>1981</b> &ndash; Ronald Reagan inaugurated as the 40th President of the United States of America.</p><p><b>1945</b> &ndash; Franklin D. Roosevelt sworn-in for an unprecedented (and never to be repeated) 4th term as US President.</p>'
            ],
            [
                '[datecalc date="6/25/1981" event="true"]',
                '<p><b>1950</b> &ndash; North Korea invades South Korea, beginning the Korean War.</p><p><b>1929</b> &ndash; US President Herbert Hoover authorizes building of Boulder Dam (Hoover Dam).</p><p><b>1876</b> &ndash; Battle of the Little Bighorn: US 7th Cavalry under Brevet Major General George Armstrong Custer wiped out by Sioux and Cheyenne warriors led by Chiefs Crazy Horse and Sitting Bull in what has become famously known as "Custer\'s Last Stand".</p>'
            ],


            // President
            [
                '[datecalc date="1/20/2009" president="true"]',
                'Barack Obama (Democratic)'
            ],
            [
                '[datecalc date="1/20/2019" president="true"]',
                'Donald J. Trump (Republican)'
            ],
            [
                '[datecalc date="1/20/1000" president="true"]',
                ''
            ],
            [
                '[datecalc date="3/04/1797" president="true"]',
                'John Adams (Federalist)'
            ],
            [
                '[datecalc date="3/05/1798" president="true"]',
                'John Adams (Federalist)'
            ],
            [
                '[datecalc date="1/18/2001" president="true"]',
                'Bill Clinton (Democratic)'
            ],
            [
                '[datecalc date="1/20/2001" president="true"]',
                'George W. Bush (Republican)'
            ],


            // NFL / NHL / NBA / MLB
            [
                '[datecalc date="1/20/2009" nfl="true"]',
                'Pittsburgh Steelers beat Arizona Cardinals (27 - 23) to win Super Bowl XLIII on February 1, 2009 in Tampa Bay, FL.'
            ],
            [
                '[datecalc date="1/20/2009" nhl="true"]',
                'Pittsburgh Penguins beat Detroit Redwings (4 - 3)'
            ],
            [
                '[datecalc date="1/20/2009" nba="true"]',
                'Los Angeles Lakers beat Orlando Magic (4 - 1)'
            ],
            [
                '[datecalc date="1/20/2009" mlb="true"]',
                'New York Yankeess beat Philadelphia Phillies (4 - 2)'
            ],

            // Power colors
            [
                '[datecalc date="1/20/2009" powercolor="true"]',
                'Blue'
            ],
            [
                '[datecalc date="6/2/2009" powercolor="true"]',
                'Yellow'
            ],
            [
                '[datecalc date="1/20/2009" powercolor="true" icon="true"]',
                '<span style="color:#0000FF">&#x2B24;</span>'
            ],
            [
                '[datecalc date="6/2/2009" powercolor="true" icon="true"]',
                '<span style="color:#FFFF00">&#x2B24;</span>'
            ],
            [
                '[datecalc date="3/9/1982" powercolor="true"]',
                'Light Green'
            ],
            [
                '[datecalc date="1/20/2009" powercolor="true" description="true"]',
                '22'
            ],
            [
                '[datecalc date="3/9/1982" powercolor="true" description="true"]',
                '33'
            ],

            // Lucky day
            [
                '[datecalc date="2/20/2009" luckyday="true"]',
                'Thursday'
            ],
            [
                '[datecalc date="6/2/2009" luckyday="true"]',
                'Wednesday'
            ],

            // Spirit Animal
            [
                '[datecalc date="2/20/2009" spiritanimal="true"]',
                'Wolf'
            ],
            [
                '[datecalc date="6/2/2009" spiritanimal="true"]',
                'Deer'
            ],

            // Automatic AM/PM
            [
                '[datecalc date="13:00:00" display="h:m"]',
                '1 PM'
            ],
            [
                '[datecalc date="13:00:00" display="h:mm"]',
                '1:00 PM'
            ],

            // Add/sub hours and minutes
            [
                '[datecalc date="00:00:00" hour="+2" display="h:mm"]',
                '2:00 AM'
            ],
            [
                '[datecalc date="12:00:00" hour="+2:00:00" display="h:m"]',
                '2 PM'
            ],
            [
                '[datecalc date="13:00:00" hour="+2:00:00" display="hh:mm"]',
                '15:00'
            ],

            // Timezone manipulation
            [
                '[datecalc date="12/14/2021 22:00:00" hour="+4:30:00" option="gmt-6" display="mmmm d, h:mm"]',
                'December 15, 1:30 AM'
            ],
            [
                '[datecalc date="12/14/2021 22:00:00" hour="+4:00:00" option="gmt-8" display="mmmm d, h:m"]',
                'December 14, 11 PM'
            ],

            // Close approximation
            [
                '[datecalc date="01/14/2020" difference="01/20/2021"]',
                '1 year ago'
            ],

            // Age
            [
                '[datecalc date="1/1/2000" age="true"]',
                '21',
                '06/25/2021'
            ],
            [
                '[datecalc date="5/23/2021" age="true"]',
                '1 month',
                '06/25/2021'
            ],
            [
                '[datecalc date="3/23/2021" age="true"]',
                '3 months',
                '06/25/2021'
            ],
            [
                '[datecalc date="6/24/2021" age="true"]',
                '1 day',
                '06/25/2021'
            ],
            [
                '[datecalc date="6/20/2021" age="true"]',
                '5 days',
                '06/25/2021'
            ],
            [
                '[datecalc date="11/22/1984" koreanage="true"]',
                '39',
                '07/25/2022'
            ],

            // Generation
            [
                '[datecalc date="6/24/2021" generation="true"]',
                'Generation Alpha'
            ],
            [
                '[datecalc date="6/24/2021" generation="true" description="true"]',
                'Generation Alpha Description'
            ],

            // Leap year
            [
                '[datecalc date="6/24/2020" leap="true"]',
                'leap year'
            ],
            [
                '[datecalc date="6/24/2021" leap="true"]',
                'not a leap year'
            ],
            [
                '[datecalc date="6/24/2020" leap="true" count="true"]',
                '366'
            ],
            [
                '[datecalc date="6/24/2021" leap="true" count="true"]',
                '365'
            ],

            // Vitals
            [
                '[datecalc date="11/22/2020" vitals="true" display="heartbeats"]',
                '24,768,000',
                '06/25/2021'
            ],
            [
                '[datecalc date="11/22/2020" vitals="true" display="blinks"]',
                '5,263,200',
                '06/25/2021'
            ],
            [
                '[datecalc date="11/22/2020" vitals="true" display="steps"]',
                '36,120,000',
                '06/25/2021'
            ],
            [
                '[datecalc date="11/22/2020" vitals="true" display="steps" abbr="true"]',
                '36.12 million',
                '06/25/2021'
            ],
            [
                '[datecalc date="11/22/2020" vitals="true" display="breaths"]',
                '4,334,400',
                '06/25/2021'
            ],
            [
                '[datecalc date="11/22/2020" vitals="true" display="miles"]',
                '18,060',
                '06/25/2021'
            ],
            [
                '[datecalc date="11/22/2020" vitals="true" display="km"]',
                '11,288',
                '06/25/2021'
            ],
            [
                '[datecalc date="11/22/2020" vitals="true" display="km" abbr="true"]',
                '11.29 thousand',
                '06/25/2021'
            ],



            // Earth travels
            [
                '[datecalc date="11/22/2020" earthtravel="true"]',
                '344,000,000 miles',
                '06/25/2021'
            ],

            // Movies
            [
                '[datecalc date="3/1/2022" netflix="true"]',
                'Christine (1983) <br />A Nightmare on Elm Street (1984) <br />Top Gun (1986) <br />The Shawshank Redemption (1994) <br />Gattaca (1997) <br />My Best Friend\'s Wedding (1997) <br />Starship Troopers (1997) <br />The Green Mile (1999) <br />Shrek (2001) <br />The Replacements (2000) <br />Shrek 2 (2004) <br />Freddy vs. Jason (2003) <br />Where the Wild Things Are (2009) <br />Coach Carter (2005) <br />V for Vendetta (2005) <br />21 (2008) <br />Public Enemies (2009) <br />A Nightmare on Elm Street (2010) <br />Due Date (2010) <br />Battleship (2012) <br />Texas Chainsaw (2013) <br />The Guardians of Justice (Will Save You!) (2022) Netflix Original<br />Redemption (2013) <br />The Gift (2015) <br />Shooter (2016) <br />Sorry to Bother You (2018) <br />Worst Roommate Ever (2022) Netflix Original<br />21 Bridges (2019) ',
            ],
            [
                '[datecalc date="3/15/2022" netflix="true"]',
                'A Walk Among the Tombstones (2014) <br />Marilyn\'s Eyes (2021) <br />Catherine Cohen: The Twist...? She\'s Gorgeous (2022) Netflix Original<br />Bad Vegan: Fame. Fraud. Fugitives. (2022) Netflix Original<br />Team Zenko Go (2022) Netflix Original<br />Adam by Eve: A Live in Animation (2022) Netflix Original<br />London Has Fallen (2016) ',
            ],
            [
                '[datecalc date="3/1/2022" netflix="true" show="2"]',
                'Christine (1983) <br />A Nightmare on Elm Street (1984) ',
            ],
            [
                '[datecalc date="3/1/2022" netflix="true" day="-1"]',
                'My Wonderful Life (2021) <br />Two Sentence Horror Stories (2017) ',
            ],
            [
                '[datecalc date="3/1/2022" netflix="true" filter="popular|Popular"]',
                'Top Gun (1986) <br />Shrek (2001) <br />Shrek 2 (2004) <br />Public Enemies (2009) ',
            ],
            [
                '[datecalc date="2/2/2022" netflix="true" filter="popular|Popular,original|Netflix Original"]',
                'Dark Desire (2020) Netflix Original',
            ],
            [
                '[datecalc date="01/10/2022" netflix="true" filter="popular|"]',
                'Undercover (2019) ',
            ],
            [
                '[datecalc date="02/14/2022" netflix="true" show="3" separator=","]',
                'Devotion (2022) ,Fishbowl Wives (2022) Netflix Original',
            ],
            [
                '[datecalc date="03/02/2022" netflix="true" data="month" show="3"]',
                '22-Mar<br />22-Mar<br />22-Mar'
            ],
            [
                '[datecalc date="03/01/2022" netflix="true" data="month" show="3" separator="<li>"]',
                '<ul><li>22-Mar<li>22-Mar<li>22-Mar</ul>'
            ],
            [
                '[datecalc date="02/01/2022" netflix="true" count="true"]',
                '33',
            ],
            [
                '[datecalc date="03/03/2022" netflix="true" filter="popular|Popular" count="true"]',
                '2',
            ],
            [
                '[datecalc date="05/01/2022" netflix="true" filter="type|movie" count="true"]',
                '30',
            ],
            [
                '[datecalc date="all" netflix="true" data="{title}, {year}, {released}" filter="date|1/1/2022" show="5"]',
                'The Hookup Plan, 2018, 12/7/2018<br />Willy Wonka & the Chocolate Factory, 1971, 6/30/1971<br />Stand by Me, 1986, 8/22/1986<br />The Lost Boys, 1987, 7/31/1987<br />Teenage Mutant Ninja Turtles, 1990, 3/30/1990',
            ],

            // Dateless
            [
                '[datecalc date="all" netflix="true" show="3" filter="original|Netflix Original"]',
                'Mud<br />Rebelde (2022) Netflix Original<br />Johnny Test (2021) Netflix Original'
            ],

            // BUGS
            [
                '[datecalc date="1/9/2022" display="dddd" count="true"]',
                '2nd Sunday'
            ],
            [
                '[datecalc date="1/9/2022" display="ddddd" count="true"]',
                '2nd Sunday'
            ],
            [
                '[datecalc date="1/7/2022" display="dddd" count="true"]',
                '1st Friday'
            ],
            [
                '[datecalc date="1/15/2022" display="dddd" count="true"]',
                '3rd Saturday'
            ],
            [
                '[datecalc date="6/15/2022" display="dddd" count="true"]',
                '3rd Wednesday'
            ],
            [
                '[datecalc date="8/11/2023" display="dddd" count="true" ordinalize="true"]',
                '2nd Friday'
            ],
            [
                '[datecalc date="6/15/2022" display="ddddd" count="true"]',
                '24th Wednesday'
            ],
            [
                '[datecalc date="1/22/1984" now="true" display="dddd"]',
                'Sunday'
            ],
            [
                '[datecalc date="1/1/2022" netflix="true" data="title" show="3" separator="<li>"]',
                '<ul><li>The Hookup Plan<li>Willy Wonka & the Chocolate Factory<li>Stand by Me</ul>'
            ],
            [
                '[datecalc date="1/1/2022" netflix="true" count="true" display="text"]',
                'fifty one'
            ],

            // Exclude filter
            [
                '[datecalc date="all" netflix="true" data="title" filter="date|1/1/2022,type||movie"]',
                'The Hookup Plan'
            ],

            // DJ
            /*[
                '[datecalc date="2018" dj="true"]',
                'The number 1 DJ in the world was Martin Garrix followed by Dimitri Vegas & Like Mike and Hardwell according to DJ Mag.'
            ],*/

            // Emoji flag
            [
                '[datecalc date="11/22/1984"  icon="United States"]',
                '🇺🇸'
            ],
            [
                '[datecalc date="11/22/1984"  icon="US"]',
                '🇺🇸'
            ],
        ];
    }

    /**
     * @dataProvider shortcode_should_output_provider
     */
    public function test_shortcode_should_output($shortcode, $output_expected, $now_override = null)
    {
        if (!empty($now_override)) {
            $shortcode = str_replace(']', ' now_override="' . $now_override . '"]', $shortcode);
        }

        $output = do_shortcode($shortcode);
        $this->assertEquals($output_expected, $output);
    }

    public function test_all_holidays_are_valid()
    {
        global $all_data;
        $holidays = $all_data['holiday'];

        $this->assertGreaterThan(0, count($holidays), 'No holidays were loaded');

        foreach ($holidays as $k => $h) {
            $date = $k;

            $key = strtotime($date);
            $this->assertNotEmpty($key, 'Error parsing holiday rule: [' . $date . ']');
        }
    }
}
