<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Metabox;


use SirsiDynix\CEPBookings\ECP\ECPIntegration;
use SirsiDynix\CEPBookings\Metabox\Inputs\MediaGalleryPicker;
use SirsiDynix\CEPBookings\Metabox\Inputs\WeeklyAvailabilityInput;
use SirsiDynix\CEPBookings\Metabox\Inputs\WPPostSelectInput;
use SirsiDynix\CEPBookings\Wordpress;

class RoomMetaboxProvider extends MetadataMetaboxProvider
{
    /**
     * RoomMetaboxProvider constructor.
     * @param Wordpress $wordpress
     * @param Wordpress\WordpressEvents $wordpressEvents
     * @param ECPIntegration $ECPIntegration
     */
    public function __construct(Wordpress $wordpress, Wordpress\WordpressEvents $wordpressEvents, ECPIntegration $ECPIntegration)
    {
        parent::__construct($wordpress, $wordpressEvents, [
            new MetaboxFieldDefinition('location', 'Location', new WPPostSelectInput($wordpress, $ECPIntegration->getVenuePostType())),
            new MetaboxFieldDefinition('room_type', 'Room Type', new WPPostSelectInput($wordpress, 'room_type')),
            new MetaboxFieldDefinition('map', 'Map', new MediaGalleryPicker($wordpress)),
            new MetaboxFieldDefinition('availability', 'Availability', new WeeklyAvailabilityInput()),
        ]);
    }
}
