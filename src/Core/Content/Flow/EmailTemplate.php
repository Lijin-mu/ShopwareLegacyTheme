<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Flow;

class EmailTemplate
{
    private const TEMPLATE_DIR = __DIR__ . '/templates/';

    private const EN_HTML_TEMPLATE = 'enquiry_form_html_en.html.twig';
    private const EN_PLAIN_TEMPLATE = 'enquiry_form_plain_en.txt.twig';
    private const DE_HTML_TEMPLATE = 'enquiry_form_html_de.html.twig';
    private const DE_PLAIN_TEMPLATE = 'enquiry_form_plain_de.txt.twig';

    /** @var array<string, string> */
    private static array $templateCache = [];

    public static function getContentHtmlEn(): string
    {
        return self::loadTemplate(self::EN_HTML_TEMPLATE);
    }

    public static function getContentPlainEn(): string
    {
        return self::loadTemplate(self::EN_PLAIN_TEMPLATE);
    }

    public static function getContentHtmlDe(): string
    {
        return self::loadTemplate(self::DE_HTML_TEMPLATE);
    }

    public static function getContentPlainDe(): string
    {
        return self::loadTemplate(self::DE_PLAIN_TEMPLATE);
    }

    private static function loadTemplate(string $file): string
    {
        if (!isset(self::$templateCache[$file])) {
            $path = self::TEMPLATE_DIR . $file;
            if (!is_file($path)) {
                throw new \RuntimeException(sprintf('Email template "%s" not found.', $file));
            }

            $content = file_get_contents($path);
            if ($content === false) {
                throw new \RuntimeException(sprintf('Failed to read email template "%s".', $file));
            }

            self::$templateCache[$file] = $content;
        }

        return self::$templateCache[$file];
    }
}

