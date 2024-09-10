<?php
/**
 * XMLSF Meta Tags CLASS
 *
 * @package XML Sitemap & Google News
 */

class XMLSF_Meta_Tags
{
    /**
     * Generate all meta tags
     *
     * @param WP_Post $post Post object.
     */
    public function generate_all_tags($post)
    {
        $this->generate_seo_tags($post);
        $this->generate_google_news_tags($post);
        $this->generate_open_graph_tags($post);
        $this->generate_twitter_tags($post);
        $this->generate_schema_org_data($post);
    }

    /**
     * Generate SEO meta tags
     *
     * @param WP_Post $post Post object.
     */
    public function generate_seo_tags($post)
    {
        $description = $this->get_description($post);
        $keywords = $this->get_keywords($post);

        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        if (!empty($keywords)) {
            echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
        }
        echo '<meta name="author" content="' . esc_attr(get_the_author_meta('display_name', $post->post_author)) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url(get_permalink($post->ID)) . '">' . "\n";
    }

    /**
     * Generate Google News meta tags
     *
     * @param WP_Post $post Post object.
     */
    public function generate_google_news_tags($post)
    {
        $keywords = $this->get_keywords($post);
        $stock_tickers = $this->get_stock_tickers($post);

        if (!empty($keywords)) {
            echo '<meta name="news_keywords" content="' . esc_attr($keywords) . '">' . "\n";
        }
        echo '<meta name="original-source" content="' . esc_url(get_permalink($post->ID)) . '">' . "\n";
        if (!empty($stock_tickers)) {
            echo '<meta name="stock_tickers" content="' . esc_attr($stock_tickers) . '">' . "\n";
        }
        echo '<meta name="publication_date" content="' . esc_attr(get_the_date('c', $post->ID)) . '">' . "\n";
    }

    /**
     * Generate Open Graph meta tags
     *
     * @param WP_Post $post Post object.
     */
    public function generate_open_graph_tags($post)
    {
        $description = $this->get_description($post);
        $image = $this->get_post_image($post);

        echo '<meta property="og:title" content="' . esc_attr(get_the_title($post->ID)) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post->ID)) . '">' . "\n";
        echo '<meta property="og:type" content="article">' . "\n";
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        }
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $post->ID)) . '">' . "\n";
        echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $post->ID)) . '">' . "\n";
        echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '">' . "\n";
    }

    /**
     * Generate Twitter meta tags
     *
     * @param WP_Post $post Post object.
     */
    public function generate_twitter_tags($post)
    {
        $description = $this->get_description($post);
        $image = $this->get_post_image($post);

        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr(get_the_title($post->ID)) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
        if ($image) {
            echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
        }
    }

    /**
     * Generate Schema.org structured data (JSON-LD)
     *
     * @param WP_Post $post Post object.
     */
    public function generate_schema_org_data($post)
    {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => get_the_title($post->ID),
            'datePublished' => get_the_date('c', $post->ID),
            'dateModified' => get_the_modified_date('c', $post->ID),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', $post->post_author)
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => $this->get_site_logo_url()
                )
            ),
            'description' => $this->get_description($post),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id' => get_permalink($post->ID)
            )
        );

        $image = $this->get_post_image($post);
        if ($image) {
            $schema['image'] = array(
                '@type' => 'ImageObject',
                'url' => $image
            );
        }

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }

    /**
     * Get post description
     *
     * @param WP_Post $post Post object.
     * @return string
     */
    private function get_description($post)
    {
        $description = get_post_meta($post->ID, 'description', true);
        if (empty($description)) {
            $description = wp_trim_words($post->post_content, 30);
        }
        return $description;
    }

    /**
     * Get post keywords
     *
     * @param WP_Post $post Post object.
     * @return string
     */
    private function get_keywords($post)
    {
        $keywords = get_post_meta($post->ID, 'keywords', true);
        return $keywords ? $keywords : '';
    }

    /**
     * Get post stock tickers
     *
     * @param WP_Post $post Post object.
     * @return string
     */
    private function get_stock_tickers($post)
    {
        $stock_tickers = get_post_meta($post->ID, 'stock_ticker', true);
        return $stock_tickers ? $stock_tickers : '';
    }

    /**
     * Get post image
     *
     * @param WP_Post $post Post object.
     * @return string|false
     */
    private function get_post_image($post)
    {
        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
            return $image[0];
        }
        return false;
    }

    /**
     * Get site logo URL
     *
     * @return string|false
     */
    private function get_site_logo_url()
    {
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
            if ($logo) {
                return $logo[0];
            }
        }
        return false;
    }
}
