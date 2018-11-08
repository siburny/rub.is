<?php
/**
 * Plugin Name: TheMovieDB Widget
 * Description: Displays a dynamic list of movies/series based on the selected criteria.
 * Author: Maxim Rubis
 * Version: 0.1.0
 */

class TMDB_Widget extends WP_Widget
{

    const BASE_URL = 'https://api.themoviedb.org/3';

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'tmdb_widget', // Base ID
            esc_html__('TMDB Widget', 'text_domain'), // Name
            array('description' => esc_html__('Shows a list of movies/series', 'text_domain')) // Args
        );
    }

    private function get_url($apiKey, $id, $is_movie)
    {
        $imdb = $this->getIMDB($apiKey, $id, $is_movie);

        if (!empty($imdb)) {
            $args = array(
                'meta_key' => 'imdb',
                'meta_value' => $imdb,
            );
            $query = new WP_Query($args);

            if ($query->have_posts()) {
                return get_permalink($query->post);
            }
        }

        return null;
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        $config = get_transient('tmdb_widget_configuration');
        if ($config === false) {
            $json = json_decode(file_get_contents('https://api.themoviedb.org/3/configuration?api_key=' . $instance['apiKey']));
            if (!empty($json) && !empty($json->images)) {
                $config = $json->images;
                set_transient('tmdb_widget_configuration', $config, WEEK_IN_SECONDS);
            } else {
                return '';
            }
        }

        $genres = get_transient('tmdb_widget_genres');
        if ($genres === false) {
            $genres = array();

            $json = json_decode(file_get_contents('https://api.themoviedb.org/3/genre/movie/list?api_key=' . $instance['apiKey']));
            if (!empty($json) && !empty($json->genres)) {
                $genres = array_merge($genres, $json->genres);
            } else {
                return '';
            }

            $json = json_decode(file_get_contents('https://api.themoviedb.org/3/genre/tv/list?api_key=' . $instance['apiKey']));
            if (!empty($json) && !empty($json->genres)) {
                $genres = array_merge($genres, $json->genres);
            } else {
                return '';
            }

            if (empty($genres)) {
                return '';
            }

            $genres = array_column($genres, 'name', 'id');

            set_transient('tmdb_widget_genres', $genres, WEEK_IN_SECONDS);
        }

        $is_movie = $instance['type'] <= 4;

        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $res = get_transient('tmdb_widget_search_' . $instance['type'] . '_' . $instance['limit']);
        if ($res === false) {
            $res = $this->callApi(array('apiKey' => $instance['apiKey'], 'type' => $instance['type'], 'limit' => $instance['limit']));
            set_transient('tmdb_widget_search_' . $instance['type'] . '_' . $instance['limit'], $res, DAY_IN_SECONDS);
        }

        if (!empty($res) && count($res) > 0) {

            echo '<style>.tmdb-table td { border: none; } .tmdb-table img { margin: 0px; } .tmdb-table td { padding-bottom: 1em; }</style>';
            echo '<table class="tmdb-table">';
            echo '<tr>';

            foreach ($res as $index => $item) {
                if ($index > 0 && $index % $instance['columns'] == 0) {
                    echo '</tr><tr>';
                }

                echo '<td style="width:' . ($instance['columns'] == 1 ? '100%' : '50%') . ';">';
                $url = $this->get_url($instance['apiKey'], $item->id, $is_movie);

                if (!empty($url)) {
                    echo '<a href="' . $url . '">';
                }
                if (!empty($instance['thumbnail']) && !empty($item->poster_path)) {
                    echo '<img src="' . $config->secure_base_url . 'w185' . $item->poster_path . '" alt="" style="' . ($instance['columns'] == 1 ? 'width:33%;float:left;margin-right:0.5em;' : 'width:75%;') . '" />';
                    if ($instance['columns'] == 1) {
                        echo '';
                    } else {
                        echo '<br />';
                    }
                }
                echo ($instance['columns'] == 1 ? '<h3>' : '') . (isset($item->title) ? $item->title : $item->name) . ($instance['columns'] == 1 ? '</h3>' : '');
                if (!empty($url)) {
                    echo '</a>';
                }

                if ($instance['columns'] == 1) {
                    if (!empty($instance['release_date']) && isset($item->release_date) && strtotime($item->release_date)) {
                        echo '<span style="font-size: 0.8em;">' . date('F j, Y', strtotime($item->release_date)) . '</span><br />';
                    }

                    if (!empty($instance['genres']) && !empty($item->genre_ids)) {
                        $g = array();
                        foreach (array_unique($item->genre_ids) as $id) {
                            if (isset($genres[$id])) {
                                $g[] = $genres[$id];
                            }
                        }
                        echo '<span style="font-size: 0.8em;">' . implode(', ', $g) . '</span>';
                    }
                }

                echo '</td>';
            }

            echo '</tr>';
            echo '</table>';
        }

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $apiKey = !empty($instance['apiKey']) ? $instance['apiKey'] : '';
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $type = !empty($instance['type']) ? $instance['type'] : 1;
        $limit = !empty($instance['limit']) ? $instance['limit'] : 5;
        $thumbnail = !empty($instance['thumbnail']) ? $instance['thumbnail'] : 0;
        $release_date = !empty($instance['release_date']) ? $instance['release_date'] : 0;
        $genres = !empty($instance['genres']) ? $instance['genres'] : 0;
        $columns = !empty($instance['columns']) ? $instance['columns'] : 2;
        ?>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('apiKey')); ?>"><?php esc_attr_e('API Key:', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('apiKey')); ?>" name="<?php echo esc_attr($this->get_field_name('apiKey')); ?>" type="text" value="<?php echo esc_attr($apiKey); ?>">
		</p>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('type')); ?>"><?php esc_attr_e('Type:', 'text_domain');?></label>
	    	<select class="widefat" id="<?php echo esc_attr($this->get_field_id('type')); ?>" name="<?php echo esc_attr($this->get_field_name('type')); ?>">
				<option value="1" <?php echo $type == 1 ? 'selected' : ''; ?>>Popular movies</option>
				<option value="2" <?php echo $type == 2 ? 'selected' : ''; ?>>Top rated movies</option>
				<option value="3" <?php echo $type == 3 ? 'selected' : ''; ?>>Upcoming movies</option>
				<option value="4" <?php echo $type == 4 ? 'selected' : ''; ?>>Now playing movies</option>
				<option value="5" <?php echo $type == 5 ? 'selected' : ''; ?>>Popular TV shows</option>
				<option value="6" <?php echo $type == 6 ? 'selected' : ''; ?>>On the air TV shows</option>
				<option value="7" <?php echo $type == 7 ? 'selected' : ''; ?>>Airing today TV shows</option>
			</select>
		</p>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php esc_attr_e('Number of items to display:', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="text" value="<?php echo esc_attr($limit); ?>">
		</p>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('thumbnail')); ?>"><?php esc_attr_e('Show thumbnail?', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('thumbnail')); ?>" name="<?php echo esc_attr($this->get_field_name('thumbnail')); ?>" type="checkbox" value="1" <?php echo !empty($thumbnail) ? 'checked="checked"' : ''; ?>>
		</p>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('release_date')); ?>"><?php esc_attr_e('Show release date?', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('release_date')); ?>" name="<?php echo esc_attr($this->get_field_name('release_date')); ?>" type="checkbox" value="1" <?php echo !empty($release_date) ? 'checked="checked"' : ''; ?>>
		</p>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('genres')); ?>"><?php esc_attr_e('Show genres?', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('genres')); ?>" name="<?php echo esc_attr($this->get_field_name('genres')); ?>" type="checkbox" value="1" <?php echo !empty($genres) ? 'checked="checked"' : ''; ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('columns')); ?>"><?php esc_attr_e('Number of columns:', 'text_domain');?></label>
	    	<select class="widefat" id="<?php echo esc_attr($this->get_field_id('columns')); ?>" name="<?php echo esc_attr($this->get_field_name('columns')); ?>">
				<option value="1" <?php echo $columns == 1 ? 'selected' : ''; ?>>1</option>
				<option value="2" <?php echo $columns == 2 ? 'selected' : ''; ?>>2</option>
			</select>
		</p>
		<?php
}

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['apiKey'] = (!empty($new_instance['apiKey'])) ? sanitize_text_field($new_instance['apiKey']) : '';
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['type'] = (!empty($new_instance['type'])) ? sanitize_text_field($new_instance['type']) : 1;
        $instance['limit'] = (!empty($new_instance['limit'])) ? sanitize_text_field($new_instance['limit']) : 5;
        $instance['thumbnail'] = (!empty($new_instance['thumbnail'])) ? 1 : 0;
        $instance['release_date'] = (!empty($new_instance['release_date'])) ? 1 : 0;
        $instance['genres'] = (!empty($new_instance['genres'])) ? 1 : 0;
        $instance['columns'] = (!empty($new_instance['columns'])) ? sanitize_text_field($new_instance['columns']) : 2;

        return $instance;
    }

    public function callApi($args)
    {
        if (empty($args['apiKey'])) {
            return array();
        }

        $args['type'] = !empty($args['type']) ? $args['type'] : 1;
        $args['limit'] = !empty($args['limit']) ? $args['limit'] : 5;

        $url = TMDB_Widget::BASE_URL;

        switch ($args['type']) {
            case 1:
                $url .= '/movie/popular?region=US&language=en-US';
                break;
            case 2:
                $url .= '/movie/top_rated?region=US&language=en-US';
                break;
            case 3:
                $url .= '/movie/upcoming?region=US&language=en-US';
                break;
            case 4:
                $url .= '/movie/now_playing?region=US&language=en-US';
                break;
            case 5:
                $url .= '/tv/popular?language=en-US';
                break;
            case 6:
                $url .= '/tv/on_the_air?language=en-US';
                break;
            case 7:
                $url .= '/tv/airing_today?language=en-US';
                break;
            case 'id':
                break;
            default:
                return array();
        }

        $json = json_decode(file_get_contents($url . '&api_key=' . $args['apiKey']));
        if (!empty($json) && is_object($json) && !empty($json->results) && is_array($json->results)) {
            return array_slice($json->results, 0, $args['limit']);
        } else {
            return array();
        }
    }

    public function getIMDB($apiKey, $id, $is_movie)
    {
        if (empty($apiKey) || empty($id)) {
            return '';
        }

        $imdb = get_transient('tmdb_widget_imdb_' . $id);
        if ($imdb !== false) {
            return $imdb;
        }

        $json = json_decode(file_get_contents('https://api.themoviedb.org/3/'.($is_movie ? 'movie' : 'tv').'/' . $id . '/external_ids?api_key=' . $apiKey));
        if (!empty($json) && is_object($json)) {
            if (!empty($json->imdb_id)) {
                $imdb = $json->imdb_id;
            } else {
                $imdb = '';
            }

            set_transient('tmdb_widget_imdb_' . $id, $imdb, WEEK_IN_SECONDS);
            return $imdb;
        } else {
            return '';
        }
    }

} // class TMDB_Widget

function register_tmdb_widget()
{
    register_widget('TMDB_Widget');
}
add_action('widgets_init', 'register_tmdb_widget');
