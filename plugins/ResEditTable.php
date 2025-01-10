<?php

Event::listen('evolution.OnManagerNodePrerender', function ($params) {
    $configs = evo()->resedittable->getConfigs();

    if (isset($configs[$params['ph']['id']])) {
        $config = evo()->resedittable->getConfig($params['ph']['id']);

        $params['ph'] = array_merge(
            $params['ph'],
            $config['tree_config'],
            [
                'tree_page_click' => route('resedittable::show', $params['ph']['id']),
                'showChildren' => '0',
            ]
        );
    }
    return serialize($params['ph']);
});
