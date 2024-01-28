<?php
/**
 * Sitemap author setting view
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_author_settings">
	<legend class="screen-reader-text">
		<?php echo esc_html( translate( 'General' ) ); ?>
	</legend>
	<p>
		<label>
			<?php esc_html_e( 'Priority', 'xml-sitemap-feed' ); ?>
			<input type="number" step="0.1" min="0.1" max="0.9" name="xmlsf_author_settings[priority]" id="xmlsf_author_priority" value="<?php echo ( isset( $author_settings['priority'] ) ? esc_attr( $author_settings['priority'] ) : '' ); ?>" class="small-text" />
		</label>
	</p>
	<p>
		<label>
			<?php esc_html_e( 'Maximum authors per sitemap', 'xml-sitemap-feed' ); ?>
			<input type="number" step="100" min="0" max="50000" name="xmlsf_author_settings[limit]" id="xmlsf_author_limit" value="<?php echo ( isset( $author_settings['limit'] ) ? esc_attr( $author_settings['limit'] ) : '' ); ?>" class="medium-text" />
		</label>
	</p>
	<p class="description">
		<?php
		echo wp_kses_post(
			apply_filters(
				'xmlsf_author_settings_description',
				sprintf(
					/* Translators: XML Sitemap Advanced */
					esc_html__( 'More options available in %s.', 'xml-sitemap-feed' ),
					'<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>'
				)
			)
		);
		?>
	</p>
</fieldset>
