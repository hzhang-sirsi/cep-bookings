<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\HTML\Elements;


use SirsiDynix\CEPBookings\HTML\ElementBuilder as EB;
use Windwalker\Dom\DomElement;
use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Form\InputElement;

class LabeledInput extends HtmlElement
{
    /**
     * LabeledInput constructor.
     * @param string            $label
     * @param DomElement|string $input
     */
    public function __construct(string $label, $input)
    {
        $labelAttr = [];
        if ($input instanceof DomElement) {
            if (($inputName = $input->getAttribute('name')) !== null) {
                $labelAttr['for'] = $inputName;
            }
        }

        parent::__construct('div', [
            EB::buildElement('label', [$label], null, null, $labelAttr),
            $input,
        ], ['class' => 'labeled-input']);
    }

    public static function build(string $label, string $type, string $id, string $name = null, string $value = null, array $attribs = [])
    {
        $input = new InputElement($type, $name, $value, array_merge($attribs, ['id' => $id]));
        return new self($label, $input);
    }
}
