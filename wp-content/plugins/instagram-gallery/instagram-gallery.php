<?php
/**
* Plugin Name: Instagram Gallery
*/

const VENDOR_CACHE = 'instagram_cache_';
const VENDOR_URL = 'https://rub.is/gallery-data/';
const VENDOR_ID = ''; //zm4go3dvaxitmjkzncbzzdtsa2

function gallery_func( $atts )
{
	$images = array();
	if( !empty($atts['at']) )
	{
		$url = '';

		$photos = 5;
		if(!empty($atts['photos']))
		{
			$photos = intval($atts['photos']);
			if(!$photos)
			{
				$photos = 5;
			}
		}

		if(substr($atts['at'], 0, 1) === '#')
		{
			$popular = true;
			if(!empty($atts['type']) && $atts['type'] != 'popular')
			{
				$popular = false;
			}
			
			$url = 'https://www.instagram.com/explore/tags/'.substr($atts['at'], 1).'/';

			$cache = get_transient(VENDOR_CACHE.$url.$popular);
			//$cache = false;
			if($cache !== FALSE)
			{
				$images = $images;
			}
			else 
			{
				try
				{
					$html = @file_get_contents($url);
				}
				catch(Exception $ex)
				{
					$html = "";
				}
				
				if(!empty($html))
				{		
					preg_match('/<script type="text\/javascript">(.*?)<\/script>/', $html, $matches);
					for($i=1;$i<count($matches);$i++)
					{
						if(strpos($matches[$i], "window._sharedData") !== FALSE)
						{
							$data = substr($matches[$i], 21, -1);
							$data = json_decode($data);
							
							if(!empty($data))
							{
								if($popular)
								{
									$data = $data->entry_data->TagPage[0]->graphql->hashtag->edge_hashtag_to_top_posts->edges;
								}
								else
								{
									$data = $data->entry_data->TagPage[0]->graphql->hashtag->edge_hashtag_to_media->edges;
								}

								if(count($data) > 0)
								{
									$c = 0;
									foreach($data as $entry)
									{
										$cache = array('url' => '', 'caption' => '', 'link' => '', 'instagram' => true);
										$cache['url'] = $entry->node->display_url;
										$cache['caption'] = $entry->node->edge_media_to_caption->edges[0]->node->text;
										$cache['link'] = 'https://www.instagram.com/p/'.$entry->node->shortcode.'/';
										$images[] = $cache;
										
										if(++$c == $photos) break;
									}
								}
							}
							
							break;
						}
					}
				}
			
				set_transient(VENDOR_CACHE.$url.$popular, $images, WEEK_IN_SECONDS);
			}
		}
		else if(substr($atts['at'], 0, 5) === 'user:')
		{
			$popular = true;
			if(!empty($atts['type']) && $atts['type'] != 'popular')
			{
				$popular = false;
			}
			
			$url = 'https://www.instagram.com/'.substr($atts['at'], 5).'/';

			$cache = get_transient(VENDOR_CACHE.$url);
			//$cache = false;
			if($cache !== FALSE && !empty($cache))
			{
				$images = $cache;
			}
			else 
			{

				try
				{
					$html = @file_get_contents($url);
				}
				catch(Exception $ex)
				{
					$html = "";
				}
				
				if(!empty($html))
				{		
					preg_match('/<script type="text\/javascript">(.*?)<\/script>/', $html, $matches);
					for($i=1;$i<count($matches);$i++)
					{
						if(strpos($matches[$i], "window._sharedData") !== FALSE)
						{
							$data = substr($matches[$i], 21, -1);
							$data = json_decode($data);
							
							if(!empty($data))
							{
								$data = $data->entry_data->ProfilePage[0]->graphql->user->edge_owner_to_timeline_media->edges;
								if(count($data) > 0)
								{
									$c = 0;
									foreach($data as $entry)
									{
										$cache = array('url' => '', 'caption' => '', 'link' => '', 'instagram' => true);
										$cache['url'] = $entry->node->display_url;
										$cache['caption'] = $entry->node->edge_media_to_caption->edges[0]->node->text;
										$cache['link'] = 'https://www.instagram.com/p/'.$entry->node->shortcode.'/';
										$cache['title'] = substr($atts['at'], 5);
										$images[] = $cache;
										
										if(++$c == $photos) break;
									}
								}
							}
							
							break;
						}
					}
				}

				set_transient(VENDOR_CACHE.$url, $images, WEEK_IN_SECONDS);
			}
		}
		//print_r($images);
	}
	elseif( !empty($atts['ids']) )
	{
		$urls = array();
		foreach(explode(",", $atts['ids']) as $image)
		{
			if (filter_var($image, FILTER_VALIDATE_URL) !== false)
			{
				$urls[] = $image;
			}
			if (filter_var($image, FILTER_VALIDATE_INT) !== false)
			{
				$urls[] = $image;
			}
		}

		foreach($urls as $url)
		{

			$cache = get_transient(VENDOR_CACHE.$url);
			$cache = false;
			if($cache !== FALSE)
			{
				$images[] = $cache;
			}
			else 
			{

				if(is_numeric($url))
				{
					/**
					* MEDIA
					*/
					
					$image = wp_get_attachment_image_src($url, 'large');
					if(!empty($image))
					{
						$cache = array('url' => '', 'caption' => '', 'name' => '');
						
						$cache['url'] = $image[0];
						$cache['caption'] = wp_get_attachment_caption($url);
						
						$images[] = $cache;
					}
				}
				else if(strpos($url, 'instagram.com/') !== FALSE)
				{
					/**
					* INSTAGRAM
					*/
				
					$html = file_get_contents($url);
					if($html !== FALSE)
					{
						$cache = array('url' => '', 'caption' => '', 'name' => '', 'instagram' => true);
						
						preg_match('/<meta property="og:image" content="(.*?)"\s?\/>/', $html, $matches);
						if(count($matches) > 1)
						{
							$cache['url'] = $matches[1];
						}

						preg_match('/<script type="text\/javascript">(.*?)<\/script>/', $html, $matches);
						for($i=1;$i<count($matches);$i++)
						{
							if(strpos($matches[$i], "window._sharedData") !== FALSE)
							{
								$data = substr($matches[$i], 21, -1);
								$data = json_decode($data);
								
								
								$cache['caption'] = $data->entry_data->PostPage[0]->graphql->shortcode_media->edge_media_to_caption->edges[0]->node->text;
								$cache['name'] = $data->entry_data->PostPage[0]->graphql->shortcode_media->owner->username;
							}
						}

						$images[] = $cache;
					}
				}
				else if(strpos($url, 'http://') !== false || strpos($url, 'https://') !== false)
				{
					$cache = array('url' => $url, 'caption' => '', 'name' => '');
					
					$images[] = $cache;
				}
				else
				{
					continue;
				}
				
				set_transient(VENDOR_CACHE.$url, $cache, WEEK_IN_SECONDS);
			}
		}
	}
		
	if(!empty($images))
	{
		if(!empty(VENDOR_ID))
		{
			//load_ad data
			$data = get_transient(VENDOR_CACHE.VENDOR_ID);
			//$data = false;
			if($data !== FALSE)
			{
			}
			else
			{
				$data = file_get_contents(VENDOR_URL.VENDOR_ID.'.txt');
				$data = base64_decode($data);
				set_transient(VENDOR_CACHE.VENDOR_ID, $data, DAY_IN_SECONDS);
			}
			$vendor_status = count($images) > 2 ? ceil(1 + count($images) / 2) : 99999;
		}
		else
		{
			$vendor_status = 99999;
		}

		ob_start();
		require("template.php");
		return ob_get_clean();
	} 
	else
	{
		return "Empty gallery";
	}
}

function init_plugin() {
	if ( current_user_can('edit_posts') || current_user_can('edit_pages') ) {
		add_action( 'print_media_templates', 'print_media_templates' );
		add_action( 'admin_head', 'admin_head' );
	}
}

function print_media_templates() {
	$current_screen = get_current_screen();
	if ( ! isset( $current_screen->id ) || $current_screen->base !== 'post' ) {
		return;
	}
	include_once('preview.html');
}

function admin_head() {
	$current_screen = get_current_screen();
	if ( ! isset( $current_screen->id ) || $current_screen->base !== 'post' ) {
		return;
	}
	wp_enqueue_script( 'gallery-editor-view', plugins_url( 'js/gallery-editor-view.js', __FILE__ ), array( 'shortcode', 'wp-util', 'jquery' ), false, true );
	
}

add_shortcode( 'gallery', 'gallery_func' );
add_action( 'admin_init', 'init_plugin', 20 );
