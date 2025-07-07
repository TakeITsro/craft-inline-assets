<?php
namespace takeitsro\inlineassets;

use Craft;
use craft\base\Plugin;
use yourplugin\extensions\InlineJsExtension;
use yourplugin\extensions\InlineCssExtension;

class InlineAssets extends Plugin
{
    public static InlineAssets $plugin;

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new InlineJsExtension());
        Craft::$app->view->registerTwigExtension(new InlineCssExtension());
    }
}
