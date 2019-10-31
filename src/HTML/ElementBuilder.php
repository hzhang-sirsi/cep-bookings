<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\HTML;


use Windwalker\Dom\HtmlElement;

class ElementBuilder
{
    /**
     * Common elements go below here. They are simple aliases to buildElement
     */

    public static function div(array $content, string $class = null, string $id = null, array $attribs = [])
    {
        return self::buildElement('div', $content, $class, $id, $attribs);
    }

    /**
     * @param string      $tag
     * @param array       $content
     * @param string|null $class
     * @param string|null $id
     * @param array       $attribs
     * @return HtmlElement
     */
    public static function buildElement(string $tag, array $content, string $class = null, string $id = null, array $attribs = [])
    {
        if ($class !== null) {
            $attribs['class'] = $class;
        }
        if ($id !== null) {
            $attribs['id'] = $id;
        }

        return new HtmlElement($tag, $content, $attribs);
    }
}
