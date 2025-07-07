<?php
namespace takeitsro\inlineassets;

use Craft;
use craft\base\Plugin;
use yii\base\Event;
use craft\web\twig\variables\CraftVariable;
use yourvendor\inlineassets\extensions\InlineJsExtension;
use yourvendor\inlineassets\extensions\InlineCssExtension;
use Twig\Environment;

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
