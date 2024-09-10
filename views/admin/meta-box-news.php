<?php
/**
 * Meta box: News
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<label
		for="xmlsf_news_custom_description"><?php esc_html_e('Custom Description:', 'xml-sitemap-feed'); ?></label><br>
	<textarea name="xmlsf_news_custom_description" id="xmlsf_news_custom_description" rows="3"
		cols="50"><?php echo esc_textarea(get_post_meta($post->ID, '_xmlsf_news_custom_description', true)); ?></textarea>
</p>
<p>
	<label for="xmlsf_news_keywords"><?php esc_html_e('Keywords:', 'xml-sitemap-feed'); ?></label><br>
	<input type="text" name="xmlsf_news_keywords" id="xmlsf_news_keywords"
		value="<?php echo esc_attr(get_post_meta($post->ID, '_xmlsf_news_keywords', true)); ?>" size="50">
</p>
<p>
	<label for="xmlsf_news_stock_tickers"><?php esc_html_e('Stock Tickers:', 'xml-sitemap-feed'); ?></label><br>
	<input type="text" name="xmlsf_news_stock_tickers" id="xmlsf_news_stock_tickers"
		value="<?php echo esc_attr(get_post_meta($post->ID, '_xmlsf_news_stock_tickers', true)); ?>" size="50">
</p>
<p>
	<label>
		<input type="checkbox" name="xmlsf_news_exclude" id="xmlsf_news_exclude" value="1" <?php checked(!empty($exclude)); ?><?php disabled($disabled); ?> />
		<?php esc_html_e('Exclude from Google News Sitemap', 'xml-sitemap-feed'); ?>
	</label>
</p>