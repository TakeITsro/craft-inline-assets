<?php
namespace modules\inlinecss;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Markup;
use Craft;

class InlineCssExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('inlineCss', [$this, 'inlineCss'], ['is_safe' => ['html']]),
        ];
    }

    public function inlineCss(string $filename): Markup
    {
        $path = Craft::getAlias('@webroot/assets/css/' . $filename);

        if (!file_exists($path)) {
            Craft::warning("CSS file not found: {$path}", __METHOD__);
            return new Markup("<!-- CSS file not found: {$filename} -->", 'utf-8');
        }

        $cacheKey = 'inline-css-' . md5($path . filemtime($path));
        $cache = Craft::$app->getCache();

        if (YII_ENV_PROD) {
            $cached = $cache->get($cacheKey);
            if ($cached instanceof Markup) {
                return $cached;
            }

            $css = file_get_contents($path);
            $processed = $this->processCss($css);
            $wrapped = $this->wrapCss($processed, $filename);
            $cache->set($cacheKey, $wrapped, 0);
            return $wrapped;
        }

        // DEV: always read fresh
        $css = file_get_contents($path);
        $processed = $this->processCss($css);
        return $this->wrapCss($processed, $filename);
    }

    private function wrapCss(string $css, string $filename): Markup
    {
        $html = "<!-- Inline CSS: {$filename} -->\n<style>{$css}</style>";
        return new Markup($html, 'utf-8');
    }

    private function processCss(string $css): string
    {
        // Replace relative ../svg/ paths with full domain path
        $css = preg_replace_callback(
            '/url\(["\']?(..\/svg\/[^"\')]+)["\']?\)/i',
            function ($matches) {
                $url = $matches[1];
                $absolute = 'https://www.takeitsro.sk/assets/svg/' . basename($url);
                return "url(\"{$absolute}\")";
            },
            $css
        );

        // Minify CSS
        $css = preg_replace('/\s+/', ' ', $css);       // Collapse whitespace
        $css = preg_replace('/;\s*/', ';', $css);      // Remove space after ;
        $css = preg_replace('/:\s*/', ':', $css);      // Remove space after :
        $css = preg_replace('/\s*{\s*/', '{', $css);   // Space around {
        $css = preg_replace('/\s*}\s*/', '}', $css);   // Space around }
        $css = trim($css);

        return $css;
    }
}
