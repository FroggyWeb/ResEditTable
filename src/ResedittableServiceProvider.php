<?php namespace EvolutionCMS\Resedittable;

use EvolutionCMS\ServiceProvider;

class ResedittableServiceProvider extends ServiceProvider {
    /**
     * Если указать пустую строку, то сниппеты и чанки будут иметь привычное нам именование
     * Допустим, файл test создаст чанк/сниппет с именем test
     * Если же указан namespace то файл test создаст чанк/сниппет с именем resedittable#test
     * При этом поддерживаются файлы в подпапках. Т.е. файл test из папки subdir создаст элемент с именем subdir/test
     */
    protected $namespace = 'resedittable';
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        /*$this->loadSnippetsFrom(
        dirname(__DIR__). '/snippets/',
        $this->namespace
        );*/
        /*$this->loadChunksFrom(
        dirname(__DIR__) . '/chunks/',
        $this->namespace
        );*/
        $this->loadPluginsFrom(
            dirname(__DIR__) . '/plugins/'
        );
        $this->app->registerRoutingModule('ResEditTable', __DIR__ . '/../routes.php', null, true);
        $this->app->alias(Controllers\Resedittable::class, 'resedittable');
        //use this code for each module what you want add
        /*$this->app->registerModule(
    'module from file',
    dirname(__DIR__).'/modules/module.php'
    );*/
    }
    public function boot() {
        $this->loadViewsFrom(__DIR__ . '/../views', 'resedittable');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'resedittable');

        $this->publishes([
            __DIR__ . '/../publishable/assets' => MODX_BASE_PATH . 'assets',
            __DIR__ . '/../publishable/configs' => EVO_CORE_PATH . 'custom/packages/directoryEditor',
        ]);
    }
}
