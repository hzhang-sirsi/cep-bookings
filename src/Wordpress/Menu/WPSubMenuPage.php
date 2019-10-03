<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Wordpress\Menu;


/**
 * @property WPMenuPage|string parent
 * @property string page_title
 * @property string menu_title
 * @property string capability
 * @property string menu_slug
 * @property callable function
 */
class WPSubMenuPage
{
    /**
     * WPMenuPage constructor.
     * @param WPMenuPage|string $parent
     * @param string $page_title
     * @param string $menu_title
     * @param string $capability
     * @param string $menu_slug
     * @param callable|null $function
     */
    public function __construct($parent, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = null)
    {
        $this->parent = $parent;
        $this->page_title = $page_title;
        $this->menu_title = $menu_title;
        $this->capability = $capability;
        $this->menu_slug = $menu_slug;
        $this->function = $function;
    }
}