<?php


namespace App\Support;


use League\CommonMark\GithubFlavoredMarkdownConverter;

class SafeMarkdownConverter
{
    protected GithubFlavoredMarkdownConverter $markdownConverter;

    public function __construct()
    {
        $this->markdownConverter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function convert(string $content): string
    {
        return $this->markdownConverter->convert($content)->getContent();
    }

    public static function parse(string $content): string
    {
        return app(static::class)->convert($content);
    }
}
