<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\Metabox\Inputs;


use SirsiDynix\CEPBookings\Wordpress;
use WP_Post;

abstract class PostMetaInput extends Input
{
    public function saveFields(Wordpress $wordpress, WP_Post $post, string $fieldName)
    {
        $processField = function (WP_Post $post, string $field, $value) use ($wordpress) {
            if (is_array($value)) {
                $value = join(' ', array_map('sanitize_text_field', $value));
            } else {
                $value = sanitize_text_field($value);
            }
            $wordpress->update_post_meta($post->ID, $field, $value);
        };

        foreach ($this->getFields($fieldName) as $field) {
            if (array_key_exists($field, $_POST)) {
                $processField($post, $field, $_POST[$field]);
            }
        }

        foreach ($this->getArrayFields($fieldName) as $field) {
            if (array_key_exists($field, $_POST)) {
                $processField($post, $field, $_POST[$field]);
            } elseif (isset($post->$field)) {
                $wordpress->update_post_meta($post->ID, $field, '');
            }
        }
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    abstract public static function getFields(string $field);

    /**
     * @param string $field
     * @return string[] Fieldnames to store as arrays
     */
    abstract public static function getArrayFields(string $field);
}
