<?php
declare(strict_types=1);

namespace SirsiDynix\CEPVenuesAssets;

use SirsiDynix\CEPVenuesAssets\Wordpress\Menu\WPMenuPage;
use SirsiDynix\CEPVenuesAssets\Wordpress\Menu\WPSubMenuPage;
use SirsiDynix\CEPVenuesAssets\Wordpress\Model\WPPostType;
use SirsiDynix\CEPVenuesAssets\Wordpress\Settings\WPSetting;
use SirsiDynix\CEPVenuesAssets\Wordpress\Settings\WPSettingsSection;
use WP_Post;
use WP_Query;

class Wordpress
{
    public static function is_installed(): bool
    {
        global $wp_version;
        return isset($wp_version);
    }

    public static function version(): string
    {
        global $wp_version;
        return $wp_version;
    }

    public static function add_action(string $event, $instance, string $function, int $priority = 10, int $nargs = 2)
    {
        return add_action($event, array($instance, $function), $priority, $nargs);
    }

    public static function add_action_fn(string $event, callable $function, int $priority = 10, int $nargs = 2)
    {
        return add_action($event, $function, $priority, $nargs);
    }

    public static function add_filter(string $tag, callable $function_to_add, int $priority = 10, $accepted_args = 1)
    {
        return add_filter($tag, $function_to_add, $priority, $accepted_args);
    }

    public static function add_settings_section(WPSettingsSection $section): void
    {
        add_settings_section(
            $section->id,
            $section->title,
            $section->labelWriter,
            $section->page->menu_slug
        );
    }

    public static function add_settings_field(WPSetting $setting): void
    {
        add_settings_field(
            $setting->name,
            $setting->title,
            $setting->field,
            $setting->section->page->menu_slug,
            $setting->section->id
        );
    }

    public static function register_setting(WPSetting $setting)
    {
        register_setting($setting->section->page->menu_slug, $setting->name);
    }

    public static function get_option(string $option_name)
    {
        return get_option($option_name);
    }

    public static function add_meta_box(string $id, string $title, callable $callback, $screen)
    {
        return add_meta_box($id, $title, $callback, $screen);
    }

    public static function get_post($post = null, string $output = OBJECT, string $filter = 'raw')
    {
        return get_post($post, $output, $filter);
    }

    /**
     * @param WP_Query $query
     * @return WP_Post[]
     */
    public static function get_posts(WP_Query $query)
    {
        return $query->get_posts();
    }

    /**
     * @param int $post_id
     * @param string $key
     * @param bool $single
     * @return mixed
     */
    public static function get_post_meta(int $post_id, string $key = '', bool $single = false)
    {
        return get_post_meta($post_id, $key, $single);
    }

    public static function add_post_meta(int $post_id, string $key, string $value, bool $unique = false)
    {
        return add_post_meta($post_id, $key, $value, $unique);
    }

    public static function update_post_meta(int $post_id, string $key, string $value, string $prev = null)
    {
        return update_post_meta($post_id, $key, $value, $prev);
    }

    public static function register_rest_route(string $namespace, string $route, array $args = array(), bool $override = false)
    {
        return register_rest_route($namespace, $route, $args, $override);
    }

    public static function get_the_terms($post_id, $taxonomy)
    {
        return get_the_terms($post_id, $taxonomy);
    }

    public static function register_post_type(WPPostType $postType)
    {
        return register_post_type($postType->name, $postType->getWpArgumentsArray());
    }

    public static function wp_is_post_revision(int $post_id)
    {
        return wp_is_post_revision($post_id);
    }

    public static function add_menu_page(WPMenuPage $menuPage)
    {
        return add_menu_page($menuPage->page_title, $menuPage->menu_title, $menuPage->capability, $menuPage->menu_slug, $menuPage->function, $menuPage->icon_url, $menuPage->position);
    }

    public static function add_sub_menu_page(WPSubMenuPage $subMenuPage)
    {
        $parentSlug = $subMenuPage->parent;
        if (!is_string($parentSlug)) {
            $parentSlug = $parentSlug->menu_slug;
        }

        return add_submenu_page($parentSlug, $subMenuPage->page_title, $subMenuPage->menu_title, $subMenuPage->capability, $subMenuPage->menu_slug, $subMenuPage->function);
    }
}
