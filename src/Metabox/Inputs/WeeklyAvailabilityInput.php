<?php
declare(strict_types=1);


namespace SirsiDynix\CEPVenuesAssets\Metabox\Inputs;


use SirsiDynix\CEPVenuesAssets\Metabox\MetaboxFieldDefinition;
use Windwalker\Dom\DomElement;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\InputElement;
use Windwalker\Html\Option;
use Windwalker\Html\Select\CheckboxList;
use WP_Post;

const WEEKLY_AVAILABILITY_STYLES = <<<'TAG'
.day-field-container input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.day-field-container label {
    height: 25px;
    width: 25px;
    text-align: center;
    margin: 2px;
    padding: 2px;
    background-color: #eee;
}

.day-field-container label:hover {
    background-color: #ccc;
}

.day-field-container input:checked + label {
    background-color: #2196F3;
}
TAG;

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
                new HtmlElement('div', [
                    new HtmlElement('label', 'Days of Week', ['style' => 'flex-grow: 1;']),
                    $this->generateDayField($post, $field->name),
                ], ['style' => 'display: flex; flex-direction: column; flex-grow: 1;']),
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
            new InputElement($type, $name, $post->$name, ['style' => 'flex-grow: 1;']),
        ], ['style' => 'display: flex; flex-direction: column; flex-grow: 1;']);
    }

    private function generateDayField(WP_Post $post, string $field)
    {
        $name = "{$field}_weekdaysAvailable";

        if (!isset($post->$name)) {
            $value = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        } else {
            $value = explode(' ', $post->$name);
        }

        return new HtmlElement('div', [
            new HtmlElement('style', WEEKLY_AVAILABILITY_STYLES),
            new CheckboxList($name, [
                new Option('S', 'sunday'),
                new Option('M', 'monday'),
                new Option('T', 'tuesday'),
                new Option('W', 'wednesday'),
                new Option('T', 'thursday'),
                new Option('F', 'friday'),
                new Option('S', 'saturday'),
            ], ['style' => 'display: flex; flex-direction: row;', 'class' => 'day-field-container'],
                $value)
        ]);
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public function getFields(string $field)
    {
        return [
            "{$field}_startDate",
            "{$field}_endDate",
            "{$field}_startTime",
            "{$field}_endTime",
        ];
    }

    /**
     * @param string $field
     * @return string[] Fieldnames to store
     */
    public function getArrayFields(string $field)
    {
        return [
            "{$field}_weekdaysAvailable",
        ];
    }
}