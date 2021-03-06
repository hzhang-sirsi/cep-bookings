<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings;

use SirsiDynix\CEPBookings\Wordpress\Menu\WPMenuPage;
use SirsiDynix\CEPBookings\Wordpress\Menu\WPSubMenuPage;
use SirsiDynix\CEPBookings\Wordpress\Model\WPPostType;
use SirsiDynix\CEPBookings\Wordpress\Settings\WPSetting;
use SirsiDynix\CEPBookings\Wordpress\Settings\WPSettingsSection;
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

    public static function get_database()
    {
        global $wpdb;
        return $wpdb;
    }

    public function add_action(string $event, $function, int $priority = 10, int $nargs = 2)
    {
        return add_action($event, $function, $priority, $nargs);
    }

    public function add_filter(string $tag, callable $function_to_add, int $priority = 10, $accepted_args = 1)
    {
        return add_filter($tag, $function_to_add, $priority, $accepted_args);
    }

    public function add_settings_section(WPSettingsSection $section): void
    {
        add_settings_section(
            $section->id,
            $section->title,
            $section->labelWriter,
            $section->page->menu_slug
        );
    }

    public function add_settings_field(WPSetting $setting): void
    {
        add_settings_field(
            $setting->name,
            $setting->title,
            $setting->field,
            $setting->section->page->menu_slug,
            $setting->section->id
        );
    }

    public function register_setting(WPSetting $setting)
    {
        register_setting($setting->section->page->menu_slug, $setting->name);
    }

    public function get_option(string $option_name)
    {
        return get_option($option_name);
    }

    public function add_meta_box(string $id, string $title, callable $callback, $screen)
    {
        return add_meta_box($id, $title, $callback, $screen);
    }

    public function get_post($post = null, string $output = 'OBJECT', string $filter = 'raw')
    {
        return get_post($post, $output, $filter);
    }

    /**
     * @param WP_Query $query
     * @return WP_Post[]
     */
    public function get_posts(WP_Query $query)
    {
        return $query->get_posts();
    }

    public function update_post_meta(int $post_id, string $key, string $value, string $prev = null)
    {
        return update_post_meta($post_id, $key, $value, $prev);
    }

    public function register_rest_route(string $namespace, string $route, array $args = array(), bool $override = false)
    {
        return register_rest_route($namespace, $route, $args, $override);
    }

    public function get_the_terms($post_id, $taxonomy)
    {
        return get_the_terms($post_id, $taxonomy);
    }

    public function register_post_type(WPPostType $postType)
    {
        return register_post_type($postType->name, $postType->getWpArgumentsArray());
    }

    public function wp_is_post_revision(int $post_id)
    {
        return wp_is_post_revision($post_id);
    }

    public function add_menu_page(WPMenuPage $menuPage)
    {
        return add_menu_page($menuPage->page_title, $menuPage->menu_title, $menuPage->capability, $menuPage->menu_slug, $menuPage->function, $menuPage->icon_url, $menuPage->position);
    }

    public function add_sub_menu_page(WPSubMenuPage $subMenuPage)
    {
        $parentSlug = $subMenuPage->parent;
        if (!is_string($parentSlug)) {
            $parentSlug = $parentSlug->menu_slug;
        }

        return add_submenu_page($parentSlug, $subMenuPage->page_title, $subMenuPage->menu_title, $subMenuPage->capability, $subMenuPage->menu_slug, $subMenuPage->function);
    }

    public function wp_register_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false)
    {
        return wp_register_script($handle, $src, $deps, $ver, $in_footer);
    }

    public function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false)
    {
        return wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
    }

    public function wp_enqueue_media()
    {
        return wp_enqueue_media();
    }

    public function wp_get_attachment_metadata($attachment_id = 0, $unfiltered = false)
    {
        return wp_get_attachment_metadata($attachment_id, $unfiltered);
    }

    public function plugins_url(string $path, string $plugin_url = null)
    {
        if ($plugin_url === null) {
            $plugin_url = Plugin::getRoot();
        }
        return plugins_url($path, $plugin_url);
    }

    public function get_current_screen()
    {
        return get_current_screen();
    }

    public function add_thickbox()
    {
        return add_thickbox();
    }

    public function wp_register_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all')
    {
        return wp_register_style($handle, $src, $deps, $ver, $media);
    }

    public function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all')
    {
        return wp_enqueue_style($handle, $src, $deps, $ver, $media);
    }

    public function wp_localize_script(string $handle, string $object_name, array $l10n)
    {
        return wp_localize_script($handle, $object_name, $l10n);
    }

    public function admin_url($path = '', $scheme = 'admin')
    {
        return admin_url($path, $scheme);
    }

    public function wp_create_nonce($action)
    {
        return wp_create_nonce($action);
    }
}
