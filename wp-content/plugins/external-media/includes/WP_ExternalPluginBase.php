<?php

/**
 * @package WP_ExternalMedia
 * External Media plugin Base class.
 */

/**
 * External Media plugin abstract class.
 * Extend this class to integrate a new file upload plugin.
 */
abstract class WP_ExternalPluginBase {

  /**
   * Import button visibilty. Master control to hide `import` button.
   * This option ignores checkbox from the settings page.
   *
   * @return boolean
   */
  public function showImportButton() {
    return TRUE;
  }

  /**
   * Link button visibility. Master control to hide `link` button.
   * This option ignores checkbox from the settings page.
   *
   * @return boolean
   */
  public function showLinkButton() {
    return TRUE;
  }

  /**
   * Some HTML text for buttons (optional).
   *
   * @return string
   */
  public function html() {
    // This could be a modal form HTML etc...
  }

  /**
   * Import button label.
   *
   * @return string
   */
  abstract public function importLabel();

  /**
   * Chooser button label.
   *
   * @return string
   */
  abstract public function chooserLabel();

  /**
   * Button ID. Used in CSS ID attribute.
   *
   * @return string.
   */
  abstract public function id();

  /**
   * Set button attributes such as data-[array-key]="[array-value]".
   *
   * @return array
   */
  abstract public function attributes( $items );

  /**
   * Icon.
   *
   * @return string
   */
  public function icon() {
    return;
  }

  /**
   * Website URL.
   *
   * @return string
   */
  public function website() {
    return;
  }

  /**
   * Load all required assets, such as Javascript or CSS.
   * Use drupal_add_js() or drupal_add_css().
   */
  abstract public function assets();

  /**
   * Configuration form.
   * Drupal form API elements.
   *
   * @return array.
   */
  public function configForm() {
    // See other plugins for examples.
  }

  /**
   * Download remote file to Drupal.
   *
   * @return public://[remote-file-name]
   * @see file_chooser_field_save_upload().
   */
  abstract public function download( $file, $filename, $caption, $referer );

  /**
   * Redirect callback.
   * Some APIs require redirect URLs. This method handles that.
   * See Plugin configuration page for the URL.
   *
   * @return string. Contents of the callback page.
   */
  public function redirectCallback() {
    // This is where you would put all required code for the response.
  }

  /**
   * Redirect URI.
   * Absolute URL to plugin callback path.
   *
   * @return string. Absolute URL.
   */
  public function redirectUri( $class ) {
    global $wp;
    $class = str_replace('\\', '-', $class);
    $url = '/index.php?external_media_plugin=' . $class;
    if ('' === get_option('permalink_structure')) {
      return get_site_url() . $url;
    }
    else {
      return home_url('/external-media/' . $class);
    }
  }

  /**
   * Callback contents.
   *
   * @return array.
   */
  public function redirectContents() {
    return array(
      'head',
      'content',
      'footer',
    );
  }

  /**
   * JSON response.
   *
   * @return array.
   */
  public function getRestResponse() {
    return array();
  }

  /**
   * Return data.
   *
   * @return data.
   */
  public function getItems() {
    // This method should be used to cache data.
    // @see Unsplash integration plugin for usage.
  }

  /**
   * Generate button attributes data-[attribute]="[value]".
   *
   * @param array $attributes. Attributes array.
   * @return string. Button data attributes.
   */
  public function renderAttributes( $attributes ) {
    $result = array();
    foreach ( $attributes as $attribute => $value ) {
      $result[] = 'data-' . trim( $attribute ) . '="' . trim( $value ) . '"';
    }
    return join( ' ', $result );
  }

  /**
   * Save image on wp_upload_dir.
   * Add image to the media library and attach in the post.
   *
   * @param string $url image url
   * @param string $plugin
   * @param string $filename
   * @param string $caption
   * @param string $referer
   * @return int $attch_id
   */
  public function save_remote_file( $url, $plugin = '', $filename = '', $caption = '', $referer = '', $options = array() ) {

    if ( !function_exists( 'curl_init' ) || empty( $filename ) ) {
      return;
    }

    // Check permissions.
    if (!current_user_can('upload_files')) {
      return;
    }

    $extensions = apply_filters( 'external_media_safe_extensions', 'jpg jpeg gif png mp3 mp4 m4v mov webm' );
    if ($this->isUnsafe( $filename, $extensions )) {
      return;
    }

    $enabled = get_option( WP_ExternalMedia_Prefix . '_prepend_plugin_name', 1 );

    if ( !empty( $plugin ) && $enabled ) {
      $filename = $plugin . '_' . $filename;
    }

    // Sanitize filename.
    $filename = sanitize_file_name( $filename );

    $upload_dir = wp_upload_dir();
    $file_path = urldecode( $upload_dir['path'] . '/' . $filename );
    $file_url = urldecode( $upload_dir['url'] . '/' . $filename );

    // Make sure we don't upload the same file more than once.
    if ( !file_exists( $file_path ) ) {

      $post_id = 0;

      setlocale( LC_ALL, "en_US.UTF8" );
      $ch = curl_init( $url );
      if ( !empty( $options['headers'] ) ) {
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $options['headers'] );
      }
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
      curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );

      // Spoof referrer to download remote media. 
      if ( !empty($referer) ) {
        curl_setopt( $ch, CURLOPT_REFERER, $referer );
      }

      $file_contents = curl_exec( $ch );

      if ( $file_contents === false ) {
        return;
      }

      $file_type = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );
      $file_size = curl_getinfo( $ch, CURLINFO_SIZE_DOWNLOAD );

      curl_close( $ch );

      $file_path = apply_filters( 'external_media_file_path', $file_path );
      file_put_contents( $file_path, $file_contents );

      $attachment = array(
        'guid'           => $file_url,
        'post_mime_type' => $file_type,
        'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'post_parent'    => $post_id,
      );
      $tcaption = trim($caption);
      if (!empty($tcaption)) {
        $attachment['post_excerpt'] = $tcaption;
      }
      $attach_id = wp_insert_attachment( $attachment, $file_path, $post_id );
      $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
      wp_update_attachment_metadata( $attach_id, $attach_data );

      set_post_thumbnail( $post_id, $attach_id );
    }
    else {
      global $wpdb;
      // Reuse already uploaded files.
      $subdir_file = ltrim( $upload_dir['subdir'], '/' )  . '/' . $filename;
      $attach_id = $wpdb->get_var( $wpdb->prepare( "SELECT pm.post_id FROM $wpdb->postmeta pm
        WHERE pm.meta_key = '_wp_attached_file' AND pm.meta_value = %s", $subdir_file ) );
    }

    return $attach_id;
  }

  /**
   * Check if file extension is unsafe to upload.
   */
  protected function isUnsafe( $filename, $extensions ) {
    if (preg_match('/\.(php|php2|php3|php4|php5|php6|php7|php8|phtml|phar|pl|py|cgi|asp|js|html|htm|xml)(\.|$)/i', $filename)) {
      $regex = '/\.(' . preg_replace('/ +/', '|', preg_quote($extensions)) . ')$/i';
      if (!preg_match($regex, $filename)) {
        return true;
      }
    }
  }

}
