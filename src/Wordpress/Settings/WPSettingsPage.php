<?php


namespace SirsiDynix\CEPVenuesAssets\Wordpress\Settings;


/**
 * @property string name
 */
class WPSettingsPage
{
    /**
     * WPSettingsPage constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string $id
     * @param string $title
     * @param callable $labelWriter
     * @return WPSettingsSection
     */
    public function createSection(string $id, string $title, callable $labelWriter)
    {
        return new WPSettingsSection($id, $title, $labelWriter, $this);
    }
}