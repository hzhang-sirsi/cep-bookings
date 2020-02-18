<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Metabox;


use SirsiDynix\CEPBookings\Metabox\Fields\MetaboxFieldDefinition;
use SirsiDynix\CEPBookings\Metabox\Inputs\Meta\GenericInput;
use SirsiDynix\CEPBookings\Metabox\Inputs\Meta\WeeklyAvailabilityInput;
use SirsiDynix\CEPBookings\Metabox\Inputs\Meta\WPPostSelectInput;
use SirsiDynix\CEPBookings\Wordpress;
use WP_Post;

class EquipmentMetaboxProvider extends MetadataMetaboxProvider
{
    /**
     * RoomMetaboxProvider constructor.
     * @param Wordpress\WordpressEvents $wordpressEvents
     * @param Wordpress                 $wordpress
     */
    public function __construct(Wordpress\WordpressEvents $wordpressEvents, Wordpress $wordpress)
    {
        parent::__construct($wordpress, $wordpressEvents, [
            new MetaboxFieldDefinition('location', 'Location', new WPPostSelectInput($wordpress, 'tribe_venue')),
            new MetaboxFieldDefinition('equipment_type', 'Equipment Type', new WPPostSelectInput($wordpress, 'equipment_type')),
            new MetaboxFieldDefinition('quantity', 'Quantity', new GenericInput('number', function (WP_Post $post, string $fieldName) {
                return $post->{$fieldName};
            }, ['min' => '0'])),
            new MetaboxFieldDefinition('availability', 'Availability', new WeeklyAvailabilityInput($wordpress)),
        ]);
    }
}
