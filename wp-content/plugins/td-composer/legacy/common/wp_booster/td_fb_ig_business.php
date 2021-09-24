<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class td_fb_ig_business {

	private static $instance = null;
	private static $fb_connected_account = array();

	public static function get_instance(){
		if ( is_null(self::$instance) ) {
			self::$instance = new td_fb_ig_business();
		}
		return self::$instance;
	}

	public function __construct() {

		$td_options_fb_connected_account = td_options::get_array('td_fb_connected_account');

		if ( !empty( $td_options_fb_connected_account ) ) {
			self::$fb_connected_account = $td_options_fb_connected_account;
		}

		if( is_admin() ) {
			add_action( 'wp_ajax_td_save_fb_account', array( $this, 'td_save_fb_account' ) );
			add_action( 'wp_ajax_td_remove_fb_page', array( $this, 'td_remove_fb_page' ) );
			add_action( 'wp_ajax_td_remove_fb_account', array( $this, 'td_remove_fb_account' ) );
			add_action( 'wp_ajax_td_remove_ig_account', array( $this, 'td_remove_ig_account' ) );
			add_action( 'wp_ajax_td_ig_remove_all', array( $this, 'td_ig_remove_all' ) );
		}
	}

	/*
	 * this function check's the validity, processes and prepares the fb account data based on a access token received from td_facebook_api (generated via code received from fb login)
	 * @param $access_token - the access token - returned from https://tagdiv.com/td_facebook_api/
	 * @param $user_id - the fb account user id - returned from https://tagdiv.com/td_facebook_api/
	 * @return bool|bool[] - fb_account_data array based on the given access token / the fb graph api/wp error / false if unknown data was received
	 */
	function td_fb_account_data_for_token( $access_token, $user_id ) {

		if ( empty( $access_token ) ) {
			return array(
				'error' => 'error - no access_token !'
			);
		}

		// here we store all data for fb account
		$fb_account_data = array();

		// fb account(user) data
		if ( $user_id ) {

			$fb_account_api_url = 'https://graph.facebook.com/' . $user_id . '?fields=name,picture&access_token=' . $access_token;
			$fb_account_api_result = wp_remote_get( $fb_account_api_url, array( 'timeout' => 60, 'sslverify' => false ) );

			if ( !is_wp_error( $fb_account_api_result ) ) {
				$fb_account_api_data = json_decode( $fb_account_api_result['body'] );
			} else {
				$fb_account_api_data_error = $fb_account_api_result;
			}

			if ( isset( $fb_account_api_data_error ) && isset( $fb_account_api_data_error->errors ) ) {
				$fb_account_api_error_message = '<b>facebook graph api $fb_account_api_url error: </b><br>';
				foreach ( $fb_account_api_data_error->errors as $key => $item ) {
					$fb_account_api_error_message .= '<div>' . $key . ': ' . $item[0] . '</div>';
				}
				$fb_account_data['fb_account_user']['error'] = $fb_account_api_error_message;
			} elseif ( !empty( $fb_account_api_data ) ) {
				$fb_account_data['fb_account_user']['name'] = $fb_account_api_data->name;
				$fb_account_data['fb_account_user']['profile_picture'] = $fb_account_api_data->picture->data->url;
			}

		}

		// fb account pages data
		$accounts_api_url = 'https://graph.facebook.com/me/accounts?fields=instagram_business_account,name,username,picture,followers_count,fan_count,access_token&limit=500&access_token=' . $access_token;
		$result = wp_remote_get( $accounts_api_url, array( 'timeout' => 60, 'sslverify' => false ) );

		if ( !is_wp_error( $result ) ) {
			$pages_data = json_decode( $result['body'] );
		} else {
			$page_error = $result;
		}

		if ( isset( $page_error ) && isset( $page_error->errors ) ) {
			$error_message = '<b>facebook graph api me/accounts error: </b><br>';
			foreach ( $page_error->errors as $key => $item ) {
				$error_message .= '<div>' . $key . ': ' . $item[0] . '</div>';
			}
			$fb_account_data['error'] = $error_message;
		} elseif( empty( $pages_data->data ) ) {
			$error_message = '';
			//$error_message = '<b>facebook graph api me/accounts empty <em>$pages_data->data</em> error: </b><br>';
			$error_message .= '<p><h3>Could not find Business Profile</h3> It looks like this Facebook Account is not currently connected to an Instagram/Facebook Business profile or theme\'s application has not been authorized to access data for any of the Instagram/Facebook Pages managed by this this Facebook Account.</p>';
			$error_message .= '<pre style="white-space: pre-wrap; word-break: break-all; display: none;"><b>$pages_data:</b><br>';
			$error_message .= print_r( $pages_data, true );
			$error_message .= '<b>$access_token:</b> ' . $access_token;
			$error_message .= '</pre>';
			$fb_account_data['error'] = $error_message;
		} else {
			foreach ( $pages_data->data as $facebook_page_data ) { // the facebook page data for pages that the user granted permissions
				$fb_account_data['fb_account_pages'][$facebook_page_data->id] = array(
					'id' => $facebook_page_data->id, // the facebook page(business) id
					'name' => $facebook_page_data->name, // the facebook page name
					'username' => $facebook_page_data->username, // the facebook page username
					'followers_count' => $facebook_page_data->followers_count, // the facebook page followers count
					'likes' => $facebook_page_data->fan_count, // the facebook page likes
					'profile_picture' => $facebook_page_data->picture->data->url, // the facebook page profile img
					'page_access_token' => $facebook_page_data->access_token, // the fb page access token
					//'instagram_business_account' => array(), // instagram business account data
				);
				if( isset( $facebook_page_data->instagram_business_account ) ) {
					$instagram_business_id = $facebook_page_data->instagram_business_account->id;
					$page_access_token = isset( $facebook_page_data->access_token ) ? $facebook_page_data->access_token : '';

					// request to get the instagram business account(page) info
					$instagram_business_account_api_url = 'https://graph.facebook.com/' . $instagram_business_id . '?fields=name,username,profile_picture_url,followers_count,media_count&access_token=' . $page_access_token;

					$result = wp_remote_get( $instagram_business_account_api_url,  array( 'timeout' => 60, 'sslverify' => false ) );
					$instagram_account_info = '{}';
					if ( !is_wp_error( $result ) ) {
						$instagram_account_info = $result['body'];
					} else {
						$page_error = $result;
					}

					$instagram_account_data = json_decode($instagram_account_info);
					if ( isset( $page_error ) && isset( $page_error->errors ) ) {
						$error_message = '<b>Instagram business account error: </b><br>';
						foreach ( $page_error->errors as $key => $item ) {
							$error_message .= $key . ': ' . $item[0] . '<br>';
						}
						$fb_account_data['fb_account_pages'][$facebook_page_data->id]['instagram_business_account']['error'] = $error_message;
					} else {
						$fb_account_data['fb_account_pages'][$facebook_page_data->id]['instagram_business_account'] = array(
							'id' => $instagram_account_data->id,
							'name' => $instagram_account_data->name,
							'username' => $instagram_account_data->username,
							'profile_picture' => $instagram_account_data->profile_picture_url,
							'followers' => $instagram_account_data->followers_count,
							'media_count' => $instagram_account_data->media_count,
						);
					}
				}
			}
		}

		if ( empty($fb_account_data) ) {
			return false;
		} else {
			return $fb_account_data;
		}
	}

	/*
	 * used to test and save fb account pages(businesses) via ajax
	 */
	function td_save_fb_account() {

		$reply = array(
			'status' => '',
			'fb_account_data' => array(),
		);

		if ( current_user_can( 'edit_posts' ) ) {

			// set post data
			$access_token = isset( $_POST['access_token'] ) ? sanitize_text_field( $_POST['access_token'] ) : '';
			$expires_in = isset( $_POST['expires_in'] ) ? (int) $_POST['expires_in'] : false;
			$user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : false;

			$fb_account_data = $this->td_fb_account_data_for_token( $access_token, $user_id ); // verifies access token and returns fb account data

			// fb account options to save in theme options
			$fb_account_data_td_options = array(
				'fb_login_access_token' => $access_token,
				'fb_login_access_token_expires_in' => $expires_in ? $expires_in : 'N/A',
				'fb_login_access_token_expires_in_ts' => $expires_in ? time() + (int) $expires_in : 'N/A',
				'account_type' => 'business',
				'fb_account_user' => $fb_account_data['fb_account_user'] ?? array(),
				'fb_account_pages_data' => array(),
			);

			// ig business accounts(pages) data options to save in theme options
			$ig_business_accounts_data_td_options = array();

			// ig business accounts(pages) connected accounts list
			$ig_business_connected_accounts_list = array();

			if ( isset( $fb_account_data['error'] ) ) {
				$reply['status'] = 'error - ' . $fb_account_data['error'];
				//$reply['status'] = 'error - ' . $fb_account_data['error'] . ' -- <em>on getting fb account pages data from access token</em>';
			} elseif ( !empty( $fb_account_data['fb_account_pages'] ) && is_array( $fb_account_data['fb_account_pages'] ) ) {
				foreach ( $fb_account_data['fb_account_pages'] as $page_id => $page_data ) {

					$fb_account_data_td_options['fb_account_pages_data'][] = $page_data;

					// if it's not an error
					if ( !isset( $page_data['instagram_business_account']['error'] ) && !empty( $page_data['instagram_business_account'] ) ) {

						// ig business page data
						$ig_business_accounts_data_td_options[$page_data['name']] = array(
							'account_type' => 'business',
							'id' => $page_data['instagram_business_account']['id'],
							'name' => $page_data['instagram_business_account']['name'],
							'username' => $page_data['instagram_business_account']['username'],
							'profile_picture' => $page_data['instagram_business_account']['profile_picture'],
							'followers' => $page_data['instagram_business_account']['followers'],
							'media_count' => $page_data['instagram_business_account']['media_count'],
							'page_access_token' => $page_data['page_access_token']
						);

						// update connected accounts(pages) list
						$ig_business_connected_accounts_list[] = $page_data['instagram_business_account']['name'];

					} else {
						// ig business page data error
						if ( isset( $page_data['instagram_business_account']['error'] ) ) {
							$ig_business_accounts_data_td_options[$page_data['name']] = array(
								'error' => $page_data['instagram_business_account']['error'] // error message
							);
						}
						//elseif ( empty( $page_data['instagram_business_account'] ) ) {
							//$ig_business_accounts_data_td_options[$page_data['name']] = array(
								// no ig business account error
								//'error' => 'No Instagram Business Account is associated with <strong>' . $page_data['name'] . '</strong> page!'
							//);
						//}
					}

				}

				// save fb account data
				td_options::update_array('td_fb_connected_account', $fb_account_data_td_options );

				// save ig business account/accounts data
				td_options::update_array('td_instagram_business_accounts', $ig_business_accounts_data_td_options );

				// set reply status for the first fb account page > ig business account
				$reply['status'] = 'success - successfully connected to the following ig business accounts: ' . implode( ',', $ig_business_connected_accounts_list );

				// process access token expiration
				if ( !empty( $fb_account_data_td_options['fb_login_access_token_expires_in_ts'] ) ) {
					$human_readable_time_string = td_human_readable_ts( $fb_account_data_td_options['fb_login_access_token_expires_in_ts'] );
					if ( strpos( $human_readable_time_string, 'ago' ) === false ) {
						$fb_account_data_td_options['fb_login_access_token_expires_in'] = '<span style="color: #0a9e01;">expires in ' . $human_readable_time_string . '</span>';
					} else {
						$fb_account_data_td_options['fb_login_access_token_expires_in'] = '<span style="color: orangered;">expired ' . $human_readable_time_string . '</span>';
					}
				}

				$reply['fb_account_data'] = $fb_account_data_td_options;
				
			} else {
				$reply['status'] = 'error - a successful connection could not be made ! ';
			}
			
		} else {
			$reply['status'] = 'error - user does not have admin rights ! ';
		}
		
		die( json_encode($reply) );
	}

	/*
	 * used to remove a connected fb account via ajax
	 */
	function td_remove_fb_account() {

		$reply = array(
			'status' => '',
		);

		// get saved fb account
		$td_options_fb_connected_account = td_options::get_array('td_fb_connected_account');
		if ( !empty( $td_options_fb_connected_account ) ) {

			// update fb account pages data option
			td_options::update_array('td_fb_connected_account', array() );

			// set reply status
			$reply['status'] = 'success - the fb business account removed !';

		} else {
			$reply['status'] = 'error - no connected fb account found !';
		}

		die( json_encode( $reply ) );

	}

	/*
	 * used to remove a fb page from connected fb account via ajax
	 */
	function td_remove_fb_page() {

		$reply = array(
			'status' => '',
		);

		if ( isset( $_POST['account_id'] ) ) {

			// get saved fb account
			$td_options_fb_connected_account = td_options::get_array('td_fb_connected_account');
			if ( !empty( $td_options_fb_connected_account ) ) {

				// remove ig account from fb connected account data
				if ( isset( $td_options_fb_connected_account['fb_account_pages_data'] ) && is_array( $td_options_fb_connected_account['fb_account_pages_data'] ) ) {

					foreach ( $td_options_fb_connected_account['fb_account_pages_data'] as $index => $page_data ) {

						if ( $_POST['account_id'] === $page_data['id'] ) {

							// empty the ig account data array
							unset( $td_options_fb_connected_account['fb_account_pages_data'][$index] );

							// set reply status
							$fb_account_pages_data_ig_account_remove_status = 'success - the <b>' . $_POST['account_username'] . '</b> page was removed from fb connected account data';

							break; // stop on first occurrence
						}
					}

					if ( empty( $fb_account_pages_data_ig_account_remove_status ) ) {
						$reply['status'] = 'warning - no instagram business account found with the id: ' . $_POST['account_id'];
					} else {
						$reply['status'] = $fb_account_pages_data_ig_account_remove_status;
					}
				} else {
					$reply['status'] = 'warning - td_options_fb_connected_account > fb_account_pages_data is not set or it is not an array';
				}

				// update fb connected account data option
				td_options::update_array('td_fb_connected_account', $td_options_fb_connected_account );

			} else {
				$reply['status'] = 'warning - no td_options_fb_connected_account found !';
			}

		} else {
			$reply['status'] = 'error - missing account id !';
		}

		die( json_encode( $reply ) );
	}

	/*
	 * used to remove a connected business ig account via ajax
	 */
	function td_remove_ig_account() {

		$reply = array(
			'status' => '',
		);

		if ( isset( $_POST['account_id'] ) ) {

			// get saved fb account
			$td_options_fb_connected_account = td_options::get_array('td_fb_connected_account');
			if ( !empty( $td_options_fb_connected_account ) ) {

				// remove ig account from fb connected account data
				if ( isset( $td_options_fb_connected_account['fb_account_pages_data'] ) && is_array( $td_options_fb_connected_account['fb_account_pages_data'] ) ) {
					foreach ( $td_options_fb_connected_account['fb_account_pages_data'] as $index => $page_data ) {

						if ( !isset( $page_data['instagram_business_account']['id'] ) ) {
							continue;
						}

						if ( $_POST['account_id'] === $page_data['instagram_business_account']['id'] ) {
							// empty the ig account data array
							$td_options_fb_connected_account['fb_account_pages_data'][$index]['instagram_business_account'] = array();
							// set reply status
							$fb_account_pages_data_ig_account_remove_status = 'success - the instagram <b>' . $_POST['account_username'] . '</b> business account was deleted from fb connected account data';

							break; // stop on first occurrence
						}
					}

					if ( empty( $fb_account_pages_data_ig_account_remove_status ) ) {
						$reply['status'] = 'warning - no instagram business account found with the id: ' . $_POST['account_id'];
					} else {
						$reply['status'] = $fb_account_pages_data_ig_account_remove_status;
					}
				} else {
					$reply['status'] = 'warning - td_options_fb_connected_account > fb_account_pages_data is not set or it is not an array';
				}

				// update fb connected account data option
				td_options::update_array('td_fb_connected_account', $td_options_fb_connected_account );

			} else {
				$reply['status'] = 'warning - no td_options_fb_connected_account found !';
			}

			// get saved ig business connected accounts
			$td_instagram_business_accounts = td_options::get_array( 'td_instagram_business_accounts');

			if ( !empty( $td_instagram_business_accounts ) && is_array( $td_instagram_business_accounts ) ) {
				foreach ( $td_instagram_business_accounts as $key => $account ) {
					if ( $_POST['account_id'] === $account['id'] ) {

						//$td_instagram_business_accounts[$key] = array(); // empty the ig account data array
						//array_splice( $td_instagram_business_accounts, $key, 1 );
						unset( $td_instagram_business_accounts[$key] );

						// also delete cached data
						$cache_key = 'td_instagram_tk_' . strtolower( $_POST['account_username'] );
						td_remote_cache::delete_item('td_instagram', $cache_key );

						// set reply status
						$ig_business_accounts_ig_account_remove_status = 'success - the instagram <b>' . $_POST['account_username'] . '</b> business account and associated cached data deleted';
					}
				}

				if ( empty( $ig_business_accounts_ig_account_remove_status ) ) {
					$reply['status'] .= 'warning - no instagram business account found with the id: ' . $_POST['account_id'];
				} else {
					$reply['status'] .= $ig_business_accounts_ig_account_remove_status;
				}

			} else {
				$reply['status'] .= 'warning - td_instagram_business_accounts is not set or it is not an array';
			}

			// update fb connected account data option
			td_options::update_array('td_instagram_business_accounts', $td_instagram_business_accounts );

		} else {
			$reply['status'] = 'error - missing account id !';
		}

		die( json_encode( $reply ) );
	}

	/*
	 * used to remove all ig connected business accounts via ajax
	 */
	function td_ig_remove_all() {

		$reply = array(
			'status' => '',
		);

		// get saved ig business connected accounts
		$td_instagram_business_accounts = td_options::get_array( 'td_instagram_business_accounts');

		if ( !empty( $td_instagram_business_accounts ) ) {

			// update fb connected account data option
			td_options::update_array('td_instagram_business_accounts', array() );

			// set reply status
			$reply['status'] = 'success - the all ig business accounts removed !';

		} else {
			$reply['status'] = 'error - instagram business accounts td options already empty !';
		}

		die( json_encode( $reply ) );
	}

}

td_fb_ig_business::get_instance();