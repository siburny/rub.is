<?php

/**
 * @package WP_ExternalMedia
 * External Media plugin class.
 */

/**
 * The OneDrive API integration class.
 */
class OneDrive extends WP_ExternalPluginBase {

  protected static $onedrive_loaded = false;

  /**
   * Implements __construct().
   */
  public function __construct() {
    add_action( 'admin_head', array( &$this, 'assets' ) );
  }

  /**
   * {@inheritdoc}
   */
  public function name() {
    return __('OneDrive');
  }

  /**
   * {@inheritdoc}
   */
  public function weight() {
    return -8;
  }

  /**
   * {@inheritdoc}
   */
  public function importLabel() {
    return __('Import from OneDrive');
  }

  /**
   * {@inheritdoc}
   */
  public function chooserLabel() {
    return __('Link to OneDrive');
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return 'one-drive-picker';
  }

  /**
   * {@inheritdoc}
   */
  public function attributes( $items ) {
    $attributes = array();
    foreach ( $items as $attribute => $value ) {
      // OneDrive doesn't support filter files by their types.
      if ( $attribute != 'mime-types' ) {
        $attributes[$attribute] = $value;
      }
    }
    return $this->renderAttributes( $attributes );
  }

  /**
   * {@inheritdoc}
   */
  public function assets() {
    if ( $this::$onedrive_loaded ) {
      return;
    }
    $onedrive_app_id = get_option( WP_ExternalMedia_Prefix . 'onedrive_app_id' );
    wp_register_script( get_class($this), plugins_url( '/plugins/js/OneDrive.js', WP_ExternalMedia_PluginName ), array( 'jquery', 'WP_ExternalMedia_admin_view_js' ) );
    wp_enqueue_script( get_class($this) );
    echo '<script type="text/javascript" src="https://js.live.net/v7.2/OneDrive.js"></script>';
    echo '<script type="text/javascript">
      var onedrive_redirect_url = \''  . esc_js($this->redirectUri( get_class($this) )) . '\';
      var onedrive_client_id = \''  . esc_js(get_option( WP_ExternalMedia_Prefix . 'onedrive_app_id' )) . '\';
    </script>';
    $this::$onedrive_loaded = true;
  }

  /**
   * {@inheritdoc}
   */
  public function configForm() {

    $elements['onedrive_app_id'] = array(
      '#title' => __('Application (client) ID'),
      '#type' => 'textfield',
      '#description' => __('Please <a href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade" target="_blank">Register your app</a> to get an Application (client) ID, if you haven\'t already done so. '
        . 'Ensure that the web page that is going to reference the SDK is a <em>Redirect URL</em> under <strong>Redirect URIs (Web platform)</strong>. Enable <strong>Implicit grant</strong> and make sure <strong>Access Tokens</strong> and <strong>ID Tokens</strong> checked. Set <strong>Treat application as a public client</strong> to Yes.'),
      '#placeholder' => __('Your Application ID'),
    );

    $elements['onedrive_redirect_uri'] = array(
      '#type' => 'markup',
      '#title' => __('Redirect URL'),
      '#markup' => '<div>' . $this->redirectUri( get_class($this) ) . '</div>',
      '#description' => __('This is the URL you will need for the redirect URL/OAuth authentication.'),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function download( $file, $filename, $caption, $referer ) {
    $attachment_id = $this->save_remote_file( $file, get_class($this), $filename, $caption, $referer );
    if ( !$attachment = wp_prepare_attachment_for_js( $attachment_id ) ) {
      wp_send_json_error();
    }
    wp_send_json_success( $attachment );
  }

  /**
   * {@inheritdoc}
   */
  public function redirectCallback() {
    $this->assets();
    return ' ';
  }

}
