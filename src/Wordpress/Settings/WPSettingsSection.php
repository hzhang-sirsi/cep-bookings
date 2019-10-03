<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Wordpress\Settings;


/**
 * @property string id
 * @property string title
 * @property callable labelWriter
 * @property WPSettingsPage page
 */
class WPSettingsSection
{
    /**
     * WPSettingsSection constructor.
     * @param string $id
     * @param string $title
     * @param callable $labelWriter
     * @param WPSettingsPage $page
     */
    public function __construct(string $id, string $title, callable $labelWriter, WPSettingsPage $page)
    {
        $this->id = $id;
        $this->title = $title;
        $this->labelWriter = $labelWriter;
        $this->page = $page;
    }

    /**
     * @param string $name
     * @param string $title
     * @param callable $field
     * @return WPSetting
     */
    public function createSetting(string $name, string $title, callable $field)
    {
        return new WPSetting($name, $title, $field, $this);
    }
}