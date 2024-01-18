<?php
/**
 * Robots.txt field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text"><?php esc_html_e( 'Additional robots.txt rules', 'xml-sitemap-feed' ); ?></legend>

	<label for="xmlsf_robots"><?php printf( /* translators: robots.txt, linked. */ esc_html__( 'Rules that will be appended to the %s generated by WordPress:', 'xml-sitemap-feed' ), '<a href="' . esc_attr( trailingslashit( get_bloginfo( 'url' ) ) ) . 'robots.txt" target="_blank">robots.txt</a>' ); ?></label>
	<br/>
	<textarea name="xmlsf_robots" id="xmlsf_robots" class="large-text" cols="50" rows="6"><?php echo esc_textarea( trim( get_option( 'xmlsf_robots', '' ) ) ); ?></textarea>
</fieldset>
