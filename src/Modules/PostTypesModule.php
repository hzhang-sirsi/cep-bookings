<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Modules;


use Exception;
use SirsiDynix\CEPBookings\Metabox\EquipmentMetaboxProvider;
use SirsiDynix\CEPBookings\Metabox\RoomMetaboxProvider;
use SirsiDynix\CEPBookings\Wordpress;
use SirsiDynix\CEPBookings\Wordpress\Menu\WPSubMenuPage;
use SirsiDynix\CEPBookings\Wordpress\Model\WPPostType;
use SirsiDynix\CEPBookings\Wordpress\WordpressEvents;
use WP_Post;

class PostTypesModule extends AbstractModule
{
    /**
     * Implement module loading
     *
     * @return void
     * @throws Exception
     */
    public function loadModule(): void
    {
        $wordpress = $this->container->get(Wordpress::class);
        $this->container->get(WordpressEvents::class)->addHandler('init', function () use ($wordpress) {
            $wordpress->register_post_type((new WPPostType('room_type', 'Room Type', 'Room Types'))
                ->setSupports(['title'])
                ->setShowInMenu(false)
            );
            $wordpress->register_post_type((new WPPostType('equipment_type', 'Equipment Type', 'Equipment Types'))
                ->setSupports(['title'])
                ->setShowInMenu(false)
            );
            $wordpress->register_post_type((new WPPostType('room'))
                ->setMenuIcon('dashicons-store')
                ->setSupports(['title', 'author', 'thumbnail'])
                ->setRegisterMetaBoxCb(array($this->container->get(RoomMetaboxProvider::class), 'registerMetabox'))
            );
            $wordpress->register_post_type((new WPPostType('equipment', 'Equipment', 'Equipment'))
                ->setMenuIcon('dashicons-screenoptions')
                ->setSupports(['title', 'author', 'thumbnail'])
                ->setRegisterMetaBoxCb(array($this->container->get(EquipmentMetaboxProvider::class), 'registerMetabox'))
            );
        });

        $wpEvents = $this->container->get(WordpressEvents::class);
        $wpEvents->addHandler('admin_init', function () use ($wordpress) {
            $wordpress->add_filter('manage_equipment_posts_columns', function ($columns) {
                return array_merge(['thumbnail' => 'Thumbnail'], $columns);
            }, 5);
            $wordpress->add_action('manage_posts_custom_column', function ($column_name, $id) {
                if ($column_name === 'thumbnail') {
                    echo get_the_post_thumbnail($id, 'thumbnail');
                }
            }, 5, 2);
        });

        $wpEvents->addHandler('admin_menu', function () use ($wordpress) {
            $wordpress->add_sub_menu_page(new WPSubMenuPage('edit.php?post_type=room', 'Room Types', 'Room Types', 'edit_posts',
                'edit.php?post_type=room_type'));
            $wordpress->add_sub_menu_page(new WPSubMenuPage('edit.php?post_type=equipment', 'Equipment Types', 'Equipment Types', 'edit_posts',
                'edit.php?post_type=equipment_type'));
        });

        $wpEvents->addHandler('save_post', function (int $post_id, WP_Post $post, bool $update = null) {
            $this->container->get(RoomMetaboxProvider::class)->savePostCallback($post_id, $post, $update);
            $this->container->get(EquipmentMetaboxProvider::class)->savePostCallback($post_id, $post, $update);
        });
    }
}
