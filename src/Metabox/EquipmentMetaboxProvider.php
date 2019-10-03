<?php
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets\Metabox;


use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\SelectInput;
use SirsiDynix\CEPVenuesAssets\Wordpress;
use WP_Query;

class EquipmentMetaboxProvider extends MetadataMetaboxProvider
{
    /**
     * RoomMetaboxProvider constructor.
     * @param Wordpress\WordpressEvents $wordpressEvents
     */
    public function __construct(Wordpress\WordpressEvents $wordpressEvents)
    {
        parent::__construct($wordpressEvents, [
            new MetaboxFieldDefinition('location', 'Location', new SelectInput(function () {
                return array_reduce(Wordpress::get_posts(new WP_Query(['post_type' => 'tribe_venue'])), function ($result, \WP_Post $post) {
                    $result[$post->ID] = $post->post_title;
                    return $result;
                }, array());
            })),
            new MetaboxFieldDefinition('equipment_type', 'Equipment Type', 'select'),
            new MetaboxFieldDefinition('equipment_image', 'Equipment Image', 'Image'),
            new MetaboxFieldDefinition('quantity', 'Quantity'),
            new MetaboxFieldDefinition('availability', 'Availability', 'Calendar Picker'),
        ]);
    }
}