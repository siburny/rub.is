<?php

class td_covid19 {

    /**
     * Get data
     *
     * @return array|boolean
     */
    static function get_data() {

        $data_from_db = self::get_option('td_covid19_data');
        $cache_timeout = HOUR_IN_SECONDS * 3;

        if( isset($data_from_db['data']) ) {

            if( ( time() - $data_from_db['timestamp'] ) > $cache_timeout ) {

                // get data for current or previous day
                $data = self::get_data_from_github();

                if( $data !== FALSE ) {
                    // if data is returned, update the db
                    $data_from_db = $data;
                    update_option('td_covid19_data', $data_from_db);
                }

            }

        } else {
            $data_from_db = self::get_data_from_github();

            if( $data_from_db !== FALSE ) {

                // if data exists, save it to the db
                update_option('td_covid19_data', $data_from_db);

            } else {
                return false;
            }

        }


        return $data_from_db;

    }



    private static function get_option($option_name) {
        $option_contents = get_option($option_name);
        if( !is_array($option_contents) ) {
            $option_contents = array();
        }

        return $option_contents;
    }



    /**
     * Check if data exists for current or previous day on github
     * and return if it does
     *
     * @return array|boolean
     */
    static function get_data_from_github() {

        $date = date('m-d-Y');
        $url = 'https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_daily_reports/' . $date . '.csv';
        $url_headers = @get_headers($url);
        $data_parsed = array();

        if($url_headers && $url_headers[0] != 'HTTP/1.1 404 Not Found') {

            // data exists for selected date, return it
            $data = file_get_contents($url);

            if( $data != '' ) {
                // data is not empty, parse it
                $data_parsed = self::parse_data($data);
            }

        }
        else {

            // data doesn't exist for current day, so check for previous day
            $date = date('m-d-Y', strtotime("yesterday"));
            $url = 'https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_daily_reports/' . $date . '.csv';
            $url_headers = @get_headers($url);

            if($url_headers && $url_headers[0] != 'HTTP/1.1 404 Not Found') {
                // data exists for previous day, return it
                $data =  file_get_contents($url);

                if( $data != '' ) {
                    // data is not empty, parse it
                    $data_parsed = self::parse_data($data);
                }
            }

        }

        // if data parsed successful, return it
        if( !empty($data_parsed) ) {
            return array(
                'date' => $date,
                'data' => $data_parsed,
                'timestamp' => time()
            );
        }

        // data doesn't exist for current or previous day, return false
        return false;

    }



    /**
     * Parse data retrieved from github
     * @param $data - the data string we have to parse
     *
     * @return array
     */
    static function parse_data($data) {

        $data_parsed = array();

        // convert data string into an array
        $data_rows = explode("\n", $data);

        // remove first row, as it only contains data headers
        unset($data_rows[0]);

        foreach ( $data_rows as $data_row ) {

            if( $data_row != NULL ) {

                $data_row_columns = str_getcsv($data_row);

                // combine duplicate countries
                if( !isset($data_parsed[$data_row_columns[3]]) ) {
                    $data_parsed[$data_row_columns[3]] = array(
                        'confirmed_cases' => $data_row_columns[7],
                        'deaths' => $data_row_columns[8],
                        'recovered' => $data_row_columns[9],
                        'active' => $data_row_columns[7] - ( $data_row_columns[8] + $data_row_columns[9] ),
                    );
                } else {
                    $active_cases = $data_row_columns[7] - ( $data_row_columns[8] + $data_row_columns[9] );

                    $data_parsed[$data_row_columns[3]]['confirmed_cases'] += $data_row_columns[7];
                    $data_parsed[$data_row_columns[3]]['deaths'] += $data_row_columns[8];
                    $data_parsed[$data_row_columns[3]]['recovered'] += $data_row_columns[9];
                    $data_parsed[$data_row_columns[3]]['active'] += $active_cases;
                }

            }

        }

        return $data_parsed;

    }

}