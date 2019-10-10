<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Modules;


use Exception;
use SirsiDynix\CEPBookings\ECP\ECPIntegration;
use SirsiDynix\CEPBookings\Metabox\EquipmentMetaboxProvider;
use SirsiDynix\CEPBookings\Metabox\EventsCalendarMetaboxProvider;
use SirsiDynix\CEPBookings\Metabox\RoomMetaboxProvider;
use SirsiDynix\CEPBookings\Wordpress\WordpressEvents;

class MetaboxEditorModule extends Module
{
    /**
     * Implement module loading
     *
     * @return void
     * @throws Exception
     */
    public function loadModule(): void
    {
        $wpEvents = $this->container->get(WordpressEvents::class);
        $wpEvents->addHandler('admin_init', function () {
            $this->container->get(EventsCalendarMetaboxProvider::class)->registerMetabox(
                $this->container->get(ECPIntegration::class)->getEventsPostType());
        });
        $wpEvents->addHandler('save_post', array($this->container->get(RoomMetaboxProvider::class), 'savePostCallback'));
        $wpEvents->addHandler('save_post', array($this->container->get(EquipmentMetaboxProvider::class), 'savePostCallback'));
    }
}