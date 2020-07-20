/**
 * @package WP_ExternalMediaSE
 * OneDrive integration.
 */

jQuery(function ($) {

  $( 'body' ).on( 'click', 'a#one-drive-picker, button#one-drive-picker',  function( e ) {
    var $type = $( this ).data( 'type' );
    var $plugin = $( this ).data( 'plugin' );
    var $cardinality = $( this ).data( 'cardinality' );
    // OneDrive plugin.
    var pickerOptions = {
      clientId: onedrive_client_id,
      action: 'download',
      success: function( files ) {
        if ( $type == 'url' ) {
          $( '#embed-url-field' ).val( files.value[0]['@microsoft.graph.downloadUrl'] ).change();
        }
        else {
          var _count = 0;
          for ( var i = 0; i < files.value.length; i++ ) {
            if ( $cardinality > 1 ) {
              if ( _count < $cardinality ) {
                external_media_upload( $plugin, files.value[i]['@microsoft.graph.downloadUrl'], files.value[i]['name'], '', '' );
                _count++;
              }
            }
          }
        }
      },
      cancel: function() {
        // handle when the user cancels picking a file
      },
      linkType: ( $type == 'url' ) ? 'webViewLink' : 'downloadLink',
      multiSelect: ( $type == 'url' ) ? false : true,
      advanced: {
        redirectUri: onedrive_redirect_url,
        scopes: 'Files.ReadWrite'
      }
    }

    OneDrive.open( pickerOptions );
    e.preventDefault();
  });

});
