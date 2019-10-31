<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Wordpress\Menu;


/**
 * @property string          $page_title
 * @property string          $menu_title
 * @property string          $capability
 * @property string          $menu_slug
 * @property callable|string $function
 * @property string          $icon_url
 * @property null            $position
 */
class WPMenuPage
{
    /**
     * WPMenuPage constructor.
     * @param string          $page_title
     * @param string          $menu_title
     * @param string          $capability
     * @param string          $menu_slug
     * @param string|callable $function
     * @param string          $icon_url
     * @param null            $position
     */
    public function __construct(string $page_title, string $menu_title, string $capability, string $menu_slug, $function = '', $icon_url = '', $position = null)
    {
        $this->page_title = $page_title;
        $this->menu_title = $menu_title;
        $this->capability = $capability;
        $this->menu_slug = $menu_slug;
        $this->function = $function;
        $this->icon_url = $icon_url;
        $this->position = $position;
    }

    /**
     * @param string          $page_title
     * @param string          $menu_title
     * @param string          $capability
     * @param string          $menu_slug
     * @param string|callable $function
     * @return WPSubMenuPage
     */
    public function createSubPage(string $page_title, string $menu_title, string $capability, string $menu_slug, $function = '')
    {
        return new WPSubMenuPage($this, $page_title, $menu_title, $capability, $menu_slug, $function);
    }
}
