<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Metabox;


use SirsiDynix\CEPVenuesAssets\Metabox\Inputs\Input;

/**
 * @property string name
 * @property string friendlyName
 * @property Input|string|null type
 */
class MetaboxFieldDefinition
{
    /**
     * MetaboxFieldDefinition constructor.
     * @param string $name
     * @param string|null $friendlyName
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

    /**
     * @return string[]
     */
    public function getFields()
    {
        if ($this->type === null) {
            return [];
        }

        if ($this->type instanceof Input) {
            return $this->type->getFields($this->name);
        }

        return [$this->name];
    }
}