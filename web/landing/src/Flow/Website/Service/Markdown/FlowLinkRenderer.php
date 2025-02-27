<?php

declare(strict_types=1);

namespace Flow\Website\Service\Markdown;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\{ChildNodeRendererInterface, NodeRendererInterface};
use League\CommonMark\Util\HtmlElement;

class FlowLinkRenderer implements NodeRendererInterface
{
    public function __construct()
    {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!$node instanceof Link) {
            throw new \InvalidArgumentException('Incompatible node type: ' . $node::class);
        }

        $attrs = $node->data->get('attributes');

        $urlParts = parse_url($node->getUrl());

        if (!isset($urlParts['scheme']) && !isset($urlParts['host'])) {
            $node->setUrl(str_replace('.md', '', $node->getUrl()));
        }

        if (str_starts_with($node->getUrl(), '/src')) {
            $node->setUrl('https://github.com/flow-php/flow/blob/1.x' . $node->getUrl());
            $attrs['target'] = '_blank';
        }

        $attrs['href'] = $node->getUrl();

        if ($node->getTitle()) {
            $attrs['title'] = $node->getTitle();
        }

        return new HtmlElement('a', $attrs, $childRenderer->renderNodes($node->children()));
    }
}
