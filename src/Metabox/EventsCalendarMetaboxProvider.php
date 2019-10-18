<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Metabox;


use SirsiDynix\CEPBookings\Metabox\Fields\MetaboxFieldDefinition;
use SirsiDynix\CEPBookings\Metabox\Inputs\EquipmentPicker;
use SirsiDynix\CEPBookings\Metabox\Inputs\RoomPicker;
use SirsiDynix\CEPBookings\Rest\Script\ClientScriptHelper;
use SirsiDynix\CEPBookings\Wordpress;
use WP_Post;

class EventsCalendarMetaboxProvider extends MetadataMetaboxProvider
{
    /**
     * RoomMetaboxProvider constructor.
     * @param Wordpress $wordpress
     * @param Wordpress\WordpressEvents $wordpressEvents
     * @param ClientScriptHelper $roomPickerAjaxScript
     * @param ClientScriptHelper $equipmentPickerAjaxScript
     */
    public function __construct(Wordpress $wordpress, Wordpress\WordpressEvents $wordpressEvents, ClientScriptHelper $roomPickerAjaxScript, ClientScriptHelper $equipmentPickerAjaxScript)
    {
        parent::__construct($wordpress, $wordpressEvents, [
            new MetaboxFieldDefinition('room_bookings', 'Room Bookings', new RoomPicker($wordpress, $roomPickerAjaxScript)),
            new MetaboxFieldDefinition('equipment_reservations', 'Equipment Reservations', new EquipmentPicker($wordpress, $equipmentPickerAjaxScript)),
        ]);
    }

    public function savePostCallback(int $post_id, WP_Post $post, bool $update = null): void
    {
        parent::savePostCallback($post_id, $post, $update);
    }
}
