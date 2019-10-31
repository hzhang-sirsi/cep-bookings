<?php
declare(strict_types=1);


namespace SirsiDynix\CEPBookings\Metabox\Fields;


use SirsiDynix\CEPBookings\Metabox\Inputs\Input;
use SirsiDynix\CEPBookings\Wordpress;
use WP_Post;

/**
 * @property string            $name
 * @property string            $friendlyName
 * @property Input|string|null $type
 */
class MetaboxFieldDefinition
{
    /**
     * MetaboxFieldDefinition constructor.
     * @param string            $name
     * @param string|null       $friendlyName
     * @param Input|string|null $type
     */
    public function __construct(string $name, string $friendlyName = null, $type = null)
    {
        $this->name = $name;

        if ($friendlyName != null) {
            $this->friendlyName = $friendlyName;
        } else {
            $this->friendlyName = ucfirst($name);
        }

        $this->type = $type;
    }

    public function saveFields(Wordpress $wordpress, WP_Post $post)
    {
        if ($this->type === null || !($this->type instanceof Input)) {
            return;
        }

        $this->type->saveFields($wordpress, $post, $this->name);
    }
}
