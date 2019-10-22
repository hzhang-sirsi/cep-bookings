<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Modules;


use Exception;
use SirsiDynix\CEPBookings\ECP\ECPIntegration;
use SirsiDynix\CEPBookings\Metabox\EventsCalendarMetaboxProvider;
use SirsiDynix\CEPBookings\Rest\Script\ClientScriptHelper;
use SirsiDynix\CEPBookings\Wordpress;
use SirsiDynix\CEPBookings\Wordpress\Ajax\EquipmentSearchHandler;
use SirsiDynix\CEPBookings\Wordpress\Ajax\RoomSearchHandler;
use SirsiDynix\CEPBookings\Wordpress\WordpressEvents;
use function DI\autowire;
use function DI\get;

class MetaboxEditorModule extends AbstractModule
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
        $wordpress = $this->container->get(Wordpress::class);

        $equipmentSearchHandler = $this->container->get(EquipmentSearchHandler::class);
        $roomSearchHandler = $this->container->get(RoomSearchHandler::class);

        $this->container->get(EquipmentSearchHandler::class)->register();
        $this->container->get(RoomSearchHandler::class)->register();

        $scriptReqs = ['jquery-modal-js', 'jquery-timepicker-js'];
        $this->container->set('EquipmentPickerClientScriptHelper', new ClientScriptHelper($wordpress,
            'equipment-picker-js', '/static/js/equipment-picker.js', $scriptReqs, 'equipmentPickerAjaxParams', [$equipmentSearchHandler]));
        $this->container->set('RoomPickerClientScriptHelper', new ClientScriptHelper($wordpress,
            'room-picker-js', '/static/js/room-picker.js', $scriptReqs, 'roomPickerAjaxParams', [$roomSearchHandler]));

        $this->container->set(EventsCalendarMetaboxProvider::class, autowire()
            ->constructorParameter('equipmentPickerAjaxScript', get('EquipmentPickerClientScriptHelper'))
            ->constructorParameter('roomPickerAjaxScript', get('RoomPickerClientScriptHelper'))
        );

        $wpEvents->addHandler('admin_init', function () use ($wordpress) {
            $this->container->get(EventsCalendarMetaboxProvider::class)->registerMetabox(
                $this->container->get(ECPIntegration::class)->getEventsPostType());
        });

        $wpEvents->addHandler('admin_enqueue_scripts', function () use ($wordpress) {
            $wordpress->wp_register_style('jquery-modal-css', '//cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css');
            $wordpress->wp_register_script('jquery-modal-js', '//cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', ['jquery']);
            $wordpress->wp_register_style('jquery-timepicker-css', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');
            $wordpress->wp_register_script('jquery-timepicker-js', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js');
            $wordpress->wp_register_script('moment-js', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment-with-locales.min.js');
        });
    }
}
