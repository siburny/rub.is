<?php
class tdm_block_inline_image extends td_block {

	protected $shortcode_atts = array(); //the atts used for rendering the current block
    private $unique_block_class;

    public function get_custom_css() {

        $compiled_css = '';

        // $unique_block_class - the unique class that is on the block. use this to target the specific instance via css
        $unique_block_class = $this->block_uid . '_rand';

        $raw_css =
            "<style>

                /* @caption_text_color */
                body .tdm_block.tdm_block_inline_image.$unique_block_class .tdm-caption {
                    color: @caption_text_color;
                }
                /* @caption_background_color */
                body .tdm_block.tdm_block_inline_image.$unique_block_class .tdm-caption {
                    padding-left: 10px;
                    padding-right: 10px;
                    background-color: @caption_background_color;
                }
                /* @caption_background_gradient */
                body .tdm_block.tdm_block_inline_image.$unique_block_class .tdm-caption {
                    padding-left: 10px;
                    padding-right: 10px;
                    @caption_background_gradient
                }

                /* @img_width */
                .$unique_block_class {
                    width: @img_width;
                }
                
				/* @overlay_color_gradient */
				.$unique_block_class .tdm-inline-image-wrap:after {
				    content: '';
				    position: absolute;
				    top: 0;
				    left: 0;
				    width: 100%;
				    height: 100%;
					@overlay_color_gradient
				}
				/* @overlay_color */
				.$unique_block_class .tdm-inline-image-wrap:after {
				    content: '';
				    position: absolute;
				    top: 0;
				    left: 0;
				    width: 100%;
				    height: 100%;
					background: @overlay_color;
				}
			
				/* @shadow */
				.$unique_block_class {
				    box-shadow: @shadow;
				}



				/* @f_caption */
				.tdm_block.$unique_block_class .tdm-caption {
					@f_caption
				}

			</style>";


        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', $this->shortcode_atts);

        $compiled_css .= $td_css_res_compiler->compile_css();
        return $compiled_css;
    }

    /**
     * Callback pe media
     *
     * @param $responsive_context td_res_context
     * @param $atts
     */
    static function cssMedia( $res_ctx ) {

        // image width
        $img_width = $res_ctx->get_shortcode_att( 'img_width' );
        $res_ctx->load_settings_raw( 'img_width', $img_width );
        if( $img_width != '' ) {
            if ( is_numeric( $img_width ) ) {
                $res_ctx->load_settings_raw( 'img_width',  $img_width . 'px;' );
            }
        }

        // shadow
        $res_ctx->load_shadow_settings( 0, 'rgba(0, 0, 0, 0.08)', 'shadow' );



        /*-- OVERLAY -- */
        // overlay color
        $res_ctx->load_color_settings( 'overlay_color', 'overlay_color', 'overlay_color_gradient', '', '');



        /*-- CAPTION -- */
        // caption text color
        $res_ctx->load_settings_raw( 'caption_text_color', $res_ctx->get_shortcode_att( 'caption_text_color' ) );

        // caption background color
        $res_ctx->load_color_settings( 'caption_background_color', 'caption_background_color', 'caption_background_gradient', '', '');



        /*-- FONTS -- */
        $res_ctx->load_font_settings( 'f_caption' );

    }

    function render($atts, $content = null) {
        parent::render($atts);

	    // $unique_block_class - the unique class that is on the block. use this to target the specific instance via css
        $this->unique_block_class = $this->block_uid . '_rand';

        $this->shortcode_atts = shortcode_atts(
			array_merge(
				td_api_multi_purpose::get_mapped_atts( __CLASS__ ))
			, $atts);

        $image = $this->get_shortcode_att( 'image' );
        $caption_text = rawurldecode( base64_decode( strip_tags( $this->get_shortcode_att( 'caption_text' ) ) ) );
        $caption_position = $this->get_shortcode_att( 'caption_position' );
        $modal_image = $this->get_shortcode_att( 'modal_image' );
        $display_inline = $this->get_shortcode_att( 'display_inline' );

	    if ( '' !== $image ) {
			$image_info = tdc_util::get_image($atts);
	    }

        $additional_classes = array();

        // display inline
        if( !empty ( $display_inline ) ) {
            $additional_classes[] = 'tdm-inline-block';
        }

        // caption position
        if( !empty ( $caption_position ) ) {
            $additional_classes[] = 'tdm-caption-over-image';
        }

        // content align horizontal
	    $content_align_horizontal = $this->get_shortcode_att( 'content_align_horizontal' );
        if( ! empty( $content_align_horizontal ) ) {
            $additional_classes[] = 'tdm-' . $content_align_horizontal;
        }

	    $buffy = '<div class="tdm_block ' . $this->get_block_classes($additional_classes) . '" ' . $this->get_block_html_atts() . '>';

	    if ( empty( $image_info['url'] ) ) {
		    $buffy .= td_util::get_block_error( 'Inline single image', "Configure this block/widget's to have an image" );
	    } else {
		    //get the block css
		    $buffy .= $this->get_block_css();

		    $buffy .= '<div class="tdm-inline-image-wrap">';
                if( !empty( $modal_image ) ) {
                    $buffy .= '<a href="' . $image_info['url'] . '">';
                        $buffy .= '<img class="tdm-image td-fix-index td-modal-image" src="' . $image_info['url'] . '" alt="">';
                    $buffy .= '</a>';
                } else {
                    $buffy .= '<img class="tdm-image td-fix-index" src="' . $image_info['url'] . '" alt="">';
                }
            $buffy .= '</div>';

            if( $caption_text != '' ) {
                $buffy .= '<div class="tdm-caption">' . $caption_text . '</div>';
            }
	    }

        $buffy .= '</div>';


        return $buffy;
    }
}