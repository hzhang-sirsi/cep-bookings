<?php /** @noinspection PhpUndefinedClassInspection */
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\ECP;

use SirsiDynix\CEPBookings\Wordpress;
use Tribe__Events__Main;
use Tribe__Events__Pro__Main;

class ECPIntegration
{
    // MD5(DISABLED)
    public const DISABLED = '055c1a591abb0e8cd86dc969727bcc0b';

    private $initialized;

    private $tribeMain;
    private $tribeEventsPro;


    /**
     * @var Wordpress
     */
    private $wordpress;

    /**
     * @var Wordpress\WordpressEvents
     */
    private $wordpressEvents;

    public function __construct(Wordpress $wordpress, Wordpress\WordpressEvents $wordpressEvents)
    {
        $this->wordpress = $wordpress;
        $this->wordpressEvents = $wordpressEvents;
    }

    public function registerHandlers()
    {
        $this->wordpressEvents->addHandler('plugins_loaded', function () {
            $this->initialized = $this->detectPlugins();
        });
    }

    private function detectPlugins(): bool
    {
        if (!self::checkDependencyVersion('Tribe__Events__Main', '4.6.0')) {
            return false;
        }

        if (!self::checkDependencyVersion('Tribe__Events__Pro__Main', '4.4.0')) {
            return false;
        }

        $this->tribeMain = Tribe__Events__Main::class;
        $this->tribeEventsPro = Tribe__Events__Pro__Main::class;

        return true;
    }

    private static function checkDependencyVersion($dependencyClass, $minimumVersion)
    {
        if (!class_exists($dependencyClass)) {
            return false;
        }

        if (!defined($dependencyClass . '::VERSION')) {
            return false;
        }

        return version_compare($dependencyClass::VERSION, $minimumVersion, '>=');
    }

    public function getOptions()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return (array)tribe_get_option('custom-fields');
    }

    public function getPostType(): string
    {
        return $this->tribeMain::POSTTYPE;
    }

    public function getGCalLink(int $postId): string
    {
        return $this->tribeMain::instance()->googleCalendarLink();
    }

    public function getICalLink(int $postId): string
    {
        $postUrl = $this->tribeMain::instance()->getLink('single', $this->wordpress->get_post($postId));
        return add_query_arg(array('ical' => 1), $postUrl);
    }

    public function getTaxonomyConstant()
    {
        return $this->tribeMain::instance()->get_event_taxonomy();
    }
}