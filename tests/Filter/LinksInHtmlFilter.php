<?php

namespace Filter;

use PHPUnit\Framework\TestCase;

class LinksInHtmlFilter extends TestCase
{
    public function testLinksInHtmlFilter()
    {
        $html = '<p>Check out <a href="https://example.com">Example</a> and <a href="https://test.com">Test</a> <a href="/relative/url">Test</a>.</p>';
        $expected = ['https://example.com', 'https://test.com', '/relative/url'];

        $filter = new \Piper\Filter\LinksInHtmlFilter();
        $result = $filter->format($html);

        $this->assertEquals($expected, $result);
    }
}
