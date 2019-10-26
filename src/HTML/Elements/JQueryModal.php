<?php
declare(strict_types=1);

namespace SirsiDynix\CEPBookings\HTML\Elements;


use Windwalker\Dom\HtmlElement;

class JQueryModal extends HtmlElement
{
    /**
     * @var string
     */
    private $id;

    /**
     * JQueryModal constructor.
     * @param string $id
     * @param string $class
     * @param array $contents
     */
    public function __construct(string $id, string $class, array $contents)
    {
        $this->id = $id;
        parent::__construct('div', $contents, ['style' => 'display: none;', 'class' => $class, 'id' => $id]);
    }

    public function createOpenButton(string $text, string $buttonId)
    {
        return new HtmlElement('a', [$text], [
            'class' => 'button', 'href' => '#' . $this->id,
            'rel' => 'modal:open', 'id' => $buttonId,
        ]);
    }
}
