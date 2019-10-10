<?php
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets\Metabox;


use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\GenericInput;
use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\SelectInput;
use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\WeeklyAvailabilityInput;
use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\WPPostSelectInput;
use SirsiDynix\CEPVenuesAssets\Wordpress;
use WP_Post;
use WP_Query;

class EquipmentMetaboxProvider extends MetadataMetaboxProvider
{
    /**
     * RoomMetaboxProvider constructor.
     * @param Wordpress\WordpressEvents $wordpressEvents
     * @param Wordpress $wordpress
     */
    public function __construct(Wordpress\WordpressEvents $wordpressEvents, Wordpress $wordpress)
    {
        parent::__construct($wordpress, $wordpressEvents, [
            new MetaboxFieldDefinition('location', 'Location', new WPPostSelectInput($wordpress, 'tribe_venue')),
            new MetaboxFieldDefinition('equipment_type', 'Equipment Type', new WPPostSelectInput($wordpress, 'equipment_type')),
            new MetaboxFieldDefinition('quantity', 'Quantity', new GenericInput('number', function (WP_Post $post, MetaboxFieldDefinition $field) {
                return $post->{$field->name};
            })),
            new MetaboxFieldDefinition('availability', 'Availability', new WeeklyAvailabilityInput()),
        ]);
    }
}