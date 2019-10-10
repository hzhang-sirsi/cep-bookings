<?php
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets\Metabox;


use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\MediaGalleryPicker;
use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\WeeklyAvailabilityInput;
use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\WPPostSelectInput;
use SirsiDynix\CEPVenuesAssets\Wordpress;

class RoomMetaboxProvider extends MetadataMetaboxProvider
{
    /**
     * RoomMetaboxProvider constructor.
     * @param Wordpress $wordpress
     * @param Wordpress\WordpressEvents $wordpressEvents
     */
    public function __construct(Wordpress $wordpress, Wordpress\WordpressEvents $wordpressEvents)
    {
        parent::__construct($wordpress, $wordpressEvents, [
            new MetaboxFieldDefinition('location', 'Location', new WPPostSelectInput($wordpress, 'tribe_venue')),
            new MetaboxFieldDefinition('room_type', 'Room Type', new WPPostSelectInput($wordpress, 'room_type')),
            new MetaboxFieldDefinition('map', 'Map', new MediaGalleryPicker($wordpress)),
            new MetaboxFieldDefinition('availability', 'Availability', new WeeklyAvailabilityInput()),
        ]);
    }
}