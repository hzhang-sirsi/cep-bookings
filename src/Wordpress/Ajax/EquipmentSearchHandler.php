<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Wordpress\Ajax;


use SirsiDynix\CEPBookings\Wordpress;

class EquipmentSearchHandler extends AjaxHandler
{
    public function getEventName(): string
    {
        return 'cb_equip_search';
    }

    public function handler(array $postData)
    {
        $equipmentType = intval($postData['equipmentType']);
        $eventDate = $postData['eventDate'];
        $startTime = $postData['startTime'];
        $endTime = $postData['endTime'];

        $response = [
            'posts' => []
        ];

        $wpdb = Wordpress::get_database();
        $queryString = <<<SQL
SELECT posts.ID AS id, posts.post_title AS title
FROM {$wpdb->posts} posts JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
WHERE posts.post_type = 'equipment' AND postmeta.meta_key = 'equipment_type' AND postmeta.meta_value = %s
SQL;
        $query = $wpdb->prepare($queryString, [$equipmentType]);
        $posts = $wpdb->get_results($query);

        foreach ($posts as $post) {
            $postId = intval($post->id);
            $thumbnail = get_the_post_thumbnail_url(intval($post->id));
            if ($thumbnail === false) {
                $thumbnail = null;
            }

            array_push($response['posts'], [
                'id' => $postId,
                'title' => $post->title,
                'thumbnail' => $thumbnail,
            ]);
        }

        return $response;
    }
}
