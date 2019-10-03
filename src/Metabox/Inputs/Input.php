<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Metabox\Inputs;


use SirsiDynix\CEPVenuesAssets\Metabox\MetaboxFieldDefinition;
use WP_Post;

interface Input
{
    public function render(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId);
}