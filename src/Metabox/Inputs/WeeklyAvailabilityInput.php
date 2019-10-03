<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Metabox\Inputs;


use SirsiDynix\CEPVenuesAssets\Metabox\MetaboxFieldDefinition;
use SirsiDynix\CEPVenuesAssets\Wordpress;
use Windwalker\Dom\DomElement;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\InputElement;
use WP_Post;

/**
 * @property string $type
 */
class WeeklyAvailabilityInput implements Input
{
    /**
     * @param WP_Post $post
     * @param MetaboxFieldDefinition $field
     * @param string $fieldId
     * @return DomElement
     */
    public function render(WP_Post $post, MetaboxFieldDefinition $field, string $fieldId)
    {
        return new HtmlElement('div', [
            $this->generateRow([
                $this->generateField($post, 'Start Date', 'date', $field->name . '_startDate'),
                $this->generateField($post, 'End Date', 'date', $field->name . '_endDate'),
            ]),
            $this->generateRow([

            ]),
            $this->generateRow([
                $this->generateField($post, 'Start Time', 'time', $field->name . '_startTime'),
                $this->generateField($post, 'End Time', 'time', $field->name . '_endTime'),
            ]),
        ], ['style' => 'display: flex; flex-direction: column; max-width: 500px; width: 100%;']);
    }

    private function generateRow(array $contents)
    {
        return new HtmlElement('div', $contents, ['style' => 'display: flex; flex-grow: 1;']);
    }

    private function generateField(WP_Post $post, string $label, string $type, string $name)
    {
        return new HtmlElement('div', [
            new HtmlElement('label', $label, ['style' => 'flex-grow: 1;']),
            new InputElement($type, $name, Wordpress::get_post_meta($post->ID, $name, true), ['style' => 'flex-grow: 1;']),
        ], ['style' => 'display: flex; flex-direction: column; flex-grow: 1;']);
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public function getFields(string $field)
    {
        return [
            $field . '_startDate',
            $field . '_endDate',
            $field . '_startTime',
            $field . '_endTime',
        ];
    }
}