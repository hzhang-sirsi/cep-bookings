<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Wordpress\Model;


use SirsiDynix\CEPBookings\Wordpress\Constants\MenuPosition;

/**
 * @property string   $name
 * @property array    $labels
 * @property string   $label
 * @property string   $description
 * @property array    $supports
 * @property array    $taxonomies
 * @property bool     $hierarchical
 * @property bool     $public
 * @property bool     $show_ui
 * @property bool     $show_in_menu
 * @property bool     $show_in_nav_menus
 * @property bool     $show_in_admin_bar
 * @property int      $menu_position
 * @property bool     $can_export
 * @property bool     $has_archive
 * @property bool     $exclude_from_search
 * @property bool     $publicly_queryable
 * @property string   $capability_type
 * @property string   $menu_icon
 * @property bool     $show_in_rest
 * @property callable $register_meta_box_cb
 */
class WPPostType
{
    /**
     * WPPostType constructor.
     * @param string      $name
     * @param string|null $singular_name
     * @param string|null $plural_name
     */
    public function __construct(string $name, string $singular_name = null, string $plural_name = null)
    {
        $this->name = $name;

        if ($singular_name === null) {
            $singular_name = ucfirst($name);
        }
        if ($plural_name === null) {
            $plural_name = ucfirst($name) . 's';
        }
        $this->labels = $this->defaultLabels($singular_name, $plural_name);

        $this->label = $name;
        $this->description = '';
        $this->supports = [];
        $this->taxonomies = [];
        $this->hierarchical = false;
        $this->public = true;
        $this->show_ui = true;
        $this->show_in_menu = true;
        $this->show_in_nav_menus = true;
        $this->show_in_admin_bar = true;
        $this->menu_position = MenuPosition::BELOW_POSTS;
        $this->menu_icon = null;
        $this->can_export = true;
        $this->has_archive = true;
        $this->exclude_from_search = false;
        $this->publicly_queryable = true;
        $this->capability_type = 'page';
        $this->show_in_rest = true;

        $this->register_meta_box_cb = null;
    }

    private function defaultLabels(string $singular_name, string $plural_name)
    {
        return array(
            'name' => $plural_name,
            'singular_name' => $singular_name,
            'menu_name' => $plural_name,
            'parent_item_colon' => 'Parent ' . $plural_name,
            'all_items' => 'All ' . $plural_name,
            'view_item' => 'View ' . $singular_name,
            'add_new_item' => 'Add New ' . $singular_name,
            'add_new' => 'Add New ' . $singular_name,
            'edit_item' => 'Edit ' . $singular_name,
            'update_item' => 'Update ' . $singular_name,
            'search_items' => 'Search ' . $plural_name,
            'not_found' => 'Not Found',
            'not_found_in_trash' => 'Not Found in Trash',
        );
    }

    public function setLabel(string $name, string $value)
    {
        $this->labels[$name] = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return WPPostType
     */
    public function setName(string $name): WPPostType
    {
        $this->name = $name;
        return $this;
    }

    public function getWpArgumentsArray()
    {
        return array(
            'label' => $this->label,
            'description' => $this->description,
            'labels' => $this->labels,
            'supports' => $this->supports,
            'taxonomies' => $this->taxonomies,
            'hierarchical' => $this->hierarchical,
            'public' => $this->public,
            'show_ui' => $this->show_ui,
            'show_in_menu' => $this->show_in_menu,
            'show_in_nav_menus' => $this->show_in_nav_menus,
            'show_in_admin_bar' => $this->show_in_admin_bar,
            'menu_position' => $this->menu_position,
            'menu_icon' => $this->menu_icon,
            'can_export' => $this->can_export,
            'has_archive' => $this->has_archive,
            'exclude_from_search' => $this->exclude_from_search,
            'publicly_queryable' => $this->publicly_queryable,
            'capability_type' => $this->capability_type,
            'show_in_rest' => $this->show_in_rest,
            'register_meta_box_cb' => $this->register_meta_box_cb,
        );
    }

    /**
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @param array $labels
     * @return WPPostType
     */
    public function setLabels(array $labels): WPPostType
    {
        $this->labels = $labels;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return WPPostType
     */
    public function setDescription(string $description): WPPostType
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return array
     */
    public function getSupports(): array
    {
        return $this->supports;
    }

    /**
     * @param array $supports
     * @return WPPostType
     */
    public function setSupports(array $supports): WPPostType
    {
        $this->supports = $supports;
        return $this;
    }

    /**
     * @return array
     */
    public function getTaxonomies(): array
    {
        return $this->taxonomies;
    }

    /**
     * @param array $taxonomies
     * @return WPPostType
     */
    public function setTaxonomies(array $taxonomies): WPPostType
    {
        $this->taxonomies = $taxonomies;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHierarchical(): bool
    {
        return $this->hierarchical;
    }

    /**
     * @param bool $hierarchical
     * @return WPPostType
     */
    public function setHierarchical(bool $hierarchical): WPPostType
    {
        $this->hierarchical = $hierarchical;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * @param bool $public
     * @return WPPostType
     */
    public function setPublic(bool $public): WPPostType
    {
        $this->public = $public;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowUi(): bool
    {
        return $this->show_ui;
    }

    /**
     * @param bool $show_ui
     * @return WPPostType
     */
    public function setShowUi(bool $show_ui): WPPostType
    {
        $this->show_ui = $show_ui;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowInMenu(): bool
    {
        return $this->show_in_menu;
    }

    /**
     * @param bool $show_in_menu
     * @return WPPostType
     */
    public function setShowInMenu(bool $show_in_menu): WPPostType
    {
        $this->show_in_menu = $show_in_menu;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowInNavMenus(): bool
    {
        return $this->show_in_nav_menus;
    }

    /**
     * @param bool $show_in_nav_menus
     * @return WPPostType
     */
    public function setShowInNavMenus(bool $show_in_nav_menus): WPPostType
    {
        $this->show_in_nav_menus = $show_in_nav_menus;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowInAdminBar(): bool
    {
        return $this->show_in_admin_bar;
    }

    /**
     * @param bool $show_in_admin_bar
     * @return WPPostType
     */
    public function setShowInAdminBar(bool $show_in_admin_bar): WPPostType
    {
        $this->show_in_admin_bar = $show_in_admin_bar;
        return $this;
    }

    /**
     * @return int
     */
    public function getMenuPosition(): int
    {
        return $this->menu_position;
    }

    /**
     * @param int $menu_position
     * @return WPPostType
     */
    public function setMenuPosition(int $menu_position): WPPostType
    {
        $this->menu_position = $menu_position;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCanExport(): bool
    {
        return $this->can_export;
    }

    /**
     * @param bool $can_export
     * @return WPPostType
     */
    public function setCanExport(bool $can_export): WPPostType
    {
        $this->can_export = $can_export;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHasArchive(): bool
    {
        return $this->has_archive;
    }

    /**
     * @param bool $has_archive
     * @return WPPostType
     */
    public function setHasArchive(bool $has_archive): WPPostType
    {
        $this->has_archive = $has_archive;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExcludeFromSearch(): bool
    {
        return $this->exclude_from_search;
    }

    /**
     * @param bool $exclude_from_search
     * @return WPPostType
     */
    public function setExcludeFromSearch(bool $exclude_from_search): WPPostType
    {
        $this->exclude_from_search = $exclude_from_search;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPubliclyQueryable(): bool
    {
        return $this->publicly_queryable;
    }

    /**
     * @param bool $publicly_queryable
     * @return WPPostType
     */
    public function setPubliclyQueryable(bool $publicly_queryable): WPPostType
    {
        $this->publicly_queryable = $publicly_queryable;
        return $this;
    }

    /**
     * @return string
     */
    public function getCapabilityType(): string
    {
        return $this->capability_type;
    }

    /**
     * @param string $capability_type
     * @return WPPostType
     */
    public function setCapabilityType(string $capability_type): WPPostType
    {
        $this->capability_type = $capability_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getMenuIcon(): string
    {
        return $this->menu_icon;
    }

    /**
     * @param string $menu_icon
     * @return WPPostType
     */
    public function setMenuIcon(string $menu_icon): WPPostType
    {
        $this->menu_icon = $menu_icon;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowInRest(): bool
    {
        return $this->show_in_rest;
    }

    /**
     * @param bool $show_in_rest
     * @return WPPostType
     */
    public function setShowInRest(bool $show_in_rest): WPPostType
    {
        $this->show_in_rest = $show_in_rest;
        return $this;
    }

    /**
     * @return callable
     */
    public function getRegisterMetaBoxCb(): callable
    {
        return $this->register_meta_box_cb;
    }

    /**
     * @param callable $register_meta_box_cb
     * @return WPPostType
     */
    public function setRegisterMetaBoxCb(callable $register_meta_box_cb): WPPostType
    {
        $this->register_meta_box_cb = $register_meta_box_cb;
        return $this;
    }
}
