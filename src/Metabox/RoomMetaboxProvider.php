<?php
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets\Metabox;


use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\SelectInput;
use SirsiDynix\CEPVenuesAssets\Wordpress;
use WP_Query;

class RoomMetaboxProvider extends MetadataMetaboxProvider
{
    /**
     * RoomMetaboxProvider constructor.
     * @param Wordpress\WordpressEvents $wordpressEvents
     */
    public function __construct(Wordpress\WordpressEvents $wordpressEvents)
    {
        parent::__construct($wordpressEvents, [
            new MetaboxFieldDefinition('location', 'Location', new SelectInput(function () {
                $ret = [];
                foreach (Wordpress::get_posts(new WP_Query(['post_type' => 'tribe_venue'])) as $post) {
                    $ret[$post->ID] = $post->post_title;
                }
                return $ret;
            })),
            new MetaboxFieldDefinition('room_type', 'Room Type', 'select'),
            new MetaboxFieldDefinition('map', 'Map', 'Image'),
            new MetaboxFieldDefinition('availability', 'Availability', 'Calendar Picker'),
        ]);
    }
}