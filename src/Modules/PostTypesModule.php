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
                return array_merge(
                    array_slice($columns, 0, 1, true),
                    ['thumbnail' => 'Thumbnail'],
                    array_slice($columns, 1, 1, true),
                    ['quantity' => 'Quantity'],
                    array_slice($columns, 2, NULL, true));
            }, 5);
            $wordpress->add_filter('manage_room_posts_columns', function ($columns) {
                return array_merge(
                    array_slice($columns, 0, 2, true),
                    ['availability' => 'Availability'],
                    ['room_type' => 'Room Type'],
                    array_slice($columns, 2, NULL, true));
            }, 5);
            $wordpress->add_action('manage_posts_custom_column', function ($column_name, $id) use ($wordpress) {
                if ($column_name === 'thumbnail') {
                    echo get_the_post_thumbnail($id, 'thumbnail');
                } elseif ($column_name === 'quantity') {
                    echo $wordpress->get_post($id)->quantity;
                } elseif ($column_name === 'availability') {
                    $labelDays = [];
                    $labelMapping = [
                        'monday' => 'M',
                        'tuesday' => 'Tu',
                        'wednesday' => 'W',
                        'thursday' => 'Th',
                        'friday' => 'F',
                        'saturday' => 'Sa',
                        'sunday' => 'Su',
                    ];
                    $daysOfWeek = explode(' ', $wordpress->get_post($id)->availability_weekdaysAvailable);
                    foreach ($daysOfWeek as $day) {
                        $label = $labelMapping[$day];
                        if ($label) {
                            array_push($labelDays, $label);
                        }
                    }

                    echo join(' ', $labelDays);
                } elseif ($column_name === 'room_type') {
                    echo $wordpress->get_post(intval($wordpress->get_post($id)->room_type))->post_title;
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
