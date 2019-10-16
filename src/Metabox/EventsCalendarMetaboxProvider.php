<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Metabox;


use SirsiDynix\CEPBookings\Wordpress;

class EventsCalendarMetaboxProvider extends MetadataMetaboxProvider
{
    /**
     * RoomMetaboxProvider constructor.
     * @param Wordpress $wordpress
     * @param Wordpress\WordpressEvents $wordpressEvents
     */
    public function __construct(Wordpress $wordpress, Wordpress\WordpressEvents $wordpressEvents)
    {
        parent::__construct($wordpress, $wordpressEvents, [
            new MetaboxFieldDefinition('room_bookings', 'Room Bookings'),
            new MetaboxFieldDefinition('equipment_reservations', 'Equipment Reservations'),
        ]);
    }
}