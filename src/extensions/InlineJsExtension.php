<?php
namespace modules\inlinejs;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Markup;
use Craft;

class InlineJsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('inlineJs', [$this, 'inlineJs'], ['is_safe' => ['html']]),
        ];
    }

    public function inlineJs(string $filename): Markup
    {
        $path = Craft::getAlias('@webroot/assets/js/' . $filename);

        if (!file_exists($path)) {
            Craft::warning("JS file not found: {$path}", __METHOD__);
            return new Markup("<!-- JS file not found: {$filename} -->", 'utf-8');
        }

        $cacheKey = 'inline-js-' . md5($path . filemtime($path));
        $cache = Craft::$app->getCache();

        if (YII_ENV_PROD) {
            $cached = $cache->get($cacheKey);
            if ($cached instanceof Markup) {
                return $cached;
            }

            // Not cached or invalid type — rebuild
            $js = file_get_contents($path);
            $minified = $this->minifyJs($js);
            $wrapped = $this->wrapJs($minified, $filename);
            $cache->set($cacheKey, $wrapped, 0); // Cache the actual Markup object
            return $wrapped;
        }

        // DEV: always read fresh
        $js = file_get_contents($path);
        $minified = $this->minifyJs($js);
        return $this->wrapJs($minified, $filename);
    }

    private function wrapJs(string $js, string $filename): Markup
    {
        $safeJs = str_replace('</script>', '<\/script>', $js);
        $html = "<!-- Inline JS: {$filename} -->\n<script>{$safeJs}</script>";
        return new Markup($html, 'utf-8');
    }

    private function minifyJs(string $js): string
    {
        // Basic minification
        $js = preg_replace('/\s+/', ' ', $js);                // Collapse whitespace
        return trim($js);
    }
}
