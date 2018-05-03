/* global tinyMCE */
(function($){
	var media = wp.media, shortcode_string = 'gallery';
	wp.mce = wp.mce || {};
	wp.mce.instagram_gallery = {
		shortcode_data: {},
		template: media.template( 'editor-instagram-gallery' ),
		getContent: function() {
			var options = this.shortcode.attrs.named;
			options.innercontent = this.shortcode.content;
			return this.template(options);
		},
		View: { // before WP 4.2:
			template: media.template( 'editor-instagram-gallery' ),
			postID: $('#post_ID').val(),
			initialize: function( options ) {
				this.shortcode = options.shortcode;
				wp.mce.instagram_gallery.shortcode_data = this.shortcode;
			},
			getHtml: function() {
				var options = this.shortcode.attrs.named;
				options.innercontent = this.shortcode.content;
				return this.template(options);
			}
		}
	};
	wp.mce.views.register( shortcode_string, wp.mce.instagram_gallery );
}(jQuery));