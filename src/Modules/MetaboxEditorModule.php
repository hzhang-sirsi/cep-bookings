<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Modules;


use Exception;
use SirsiDynix\CEPBookings\ECP\ECPIntegration;
use SirsiDynix\CEPBookings\Metabox\EventsCalendarMetaboxProvider;
use SirsiDynix\CEPBookings\Wordpress;
use SirsiDynix\CEPBookings\Wordpress\WordpressEvents;

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
        $wpEvents->addHandler('admin_init', function () {
            $this->container->get(EventsCalendarMetaboxProvider::class)->registerMetabox(
                $this->container->get(ECPIntegration::class)->getEventsPostType());
        });

        $wpEvents->addHandler('admin_enqueue_scripts', function () {
            $wordpress = $this->container->get(Wordpress::class);
            $wordpress->wp_register_style('jquery-modal-css', '//cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css');
            $wordpress->wp_register_script('jquery-modal-js', '//cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', ['jquery']);
            $wordpress->wp_register_style('jquery-timepicker-css', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');
            $wordpress->wp_register_script('jquery-timepicker-js', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js');
        });
    }
}
