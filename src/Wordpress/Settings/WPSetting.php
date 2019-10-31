<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Wordpress\Settings;


/**
 * @property string            $name
 * @property string            $title
 * @property callable          $field
 * @property WPSettingsSection $section
 */
class WPSetting
{
    /**
     * WPSetting constructor.
     * @param string            $name
     * @param string            $title
     * @param callable          $field
     * @param WPSettingsSection $section
     */
    public function __construct(string $name, string $title, callable $field, WPSettingsSection $section)
    {
        $this->name = $name;
        $this->title = $title;
        $this->field = $field;
        $this->section = $section;
    }
}
