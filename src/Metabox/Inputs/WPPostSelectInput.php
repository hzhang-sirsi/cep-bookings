<?php


namespace SirsiDynix\CEPVenuesAssets\Metabox\Inputs;


use SirsiDynix\CEPVenuesAssets\Wordpress;
use WP_Post;
use WP_Query;

class WPPostSelectInput extends SelectInput
{
    public function __construct(Wordpress $wordpress, string $postType)
    {
        parent::__construct(function () use ($wordpress, $postType) { return $this->getOptions($wordpress, $postType); });
    }

    public function getOptions(Wordpress $wordpress, string $postType)
    {
        return array_reduce($wordpress->get_posts(new WP_Query(['post_type' => $postType])), function ($result, WP_Post $post) {
            $result[$post->ID] = $post->post_title;
            return $result;
        }, array());
    }
}