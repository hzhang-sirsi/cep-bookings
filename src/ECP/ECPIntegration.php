<?php /** @noinspection PhpUndefinedClassInspection */
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets\ECP;

use SirsiDynix\CEPVenuesAssets\Wordpress;
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
     * @var Wordpress\WordpressEvents
     */
    private $wordpressEvents;

    public function __construct(Wordpress\WordpressEvents $wordpressEvents)
    {
        $this->wordpressEvents = $wordpressEvents;
    }

    public function registerHandlers()
    {
        $this->wordpressEvents->addHandler('plugins_loaded', function() {
            $this->initialized = $this->detectPlugins();
        });
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
        $postUrl = $this->tribeMain::instance()->getLink('single', Wordpress::get_post($postId));
        return add_query_arg(array('ical' => 1), $postUrl);
    }

    public function getTaxonomyConstant()
    {
        return $this->tribeMain::instance()->get_event_taxonomy();
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

        add_filter('tribe_get_event_link', function ($link, $postId) {
            /** @noinspection PhpUndefinedFunctionInspection */
            $website_url = tribe_get_event_website_url($postId);
            // Only swaps link if set
            if (!empty($website_url)) {
                $link = $website_url;
            }
            return $link;
        }, 100, 2);

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
}