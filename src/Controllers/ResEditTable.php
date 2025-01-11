<?php

namespace EvolutionCMS\Resedittable\Controllers;

use DocumentManager;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvar;

class ResEditTable {

    private $configs;

    public function getConfigs() {
        if ($this->configs !== null) {
            return $this->configs;
        }

        $configs = [];

        foreach (glob(EVO_CORE_PATH . 'custom/packages/resedittable/configs/*.php') as $entry) {
            $config = include $entry;

            if (is_array($config) && isset($config['ids'])) {
                foreach ($config['ids'] as $id) {
                    $configs[$id] = $config;
                }
            }
        }
        return $this->configs = $configs;
    }

    public function getConfig($id) {
        $configs = $this->getConfigs();

        if (isset($configs[$id])) {
            $config = $configs[$id];
            $default = $this->getDefaultConfig();
            $config['columns'] = array_merge($default['columns'], $config['columns'] ?? []);
            $config['tree_config'] = array_merge($default['tree_config'], $config['tree_config'] ?? []);
            $config = array_merge($default, $config);
            $config['lang'] = array_merge(__('resedittable::messages'), $config['lang']);

            $sort = 0;
            $config['columns'] = array_map(function ($column) use (&$sort) {
                if (!isset($column['order'])) {
                    $column['order'] = $sort++;
                }
                return $column;
            }, $config['columns']);

            uasort($config['columns'], function ($a, $b) {
                if (!isset($a['sort']) || !isset($b['sort'])) {
                    return 0;
                }

                return $a['sort'] - $b['sort'];
            });

            $config['id'] = $id;

            return $config;
        }

        return null;
    }

    private function getDefaultConfig() {
        return [
            'show_actions' => true,
        ];
    }
    public function getResources(SiteContent $parent, array $config, $limit = 5000) {
        $names = array_keys($config['columns']);

        $tvs = $this->getTmplvarsValues($names);
        $id = ($config['show-children']) ? $parent->getAllChildren($parent) : $parent->id;

        $items = $parent->query()
            ->whereIn('site_content.id', $id)
            ->withTVs($names)
            ->withTrashed()
            ->when(isset($config['query']), function ($query) use ($config) {
                return call_user_func($config['query'], $query);
            });
        // $items = (new Filters())->injectFilters($items, array_keys($config['columns']));

        $items = $items
            ->orderBy('isfolder', 'desc')
            ->orderBy('menuindex')
            ->paginate($limit)
            ->through(function ($item) use ($config, $tvs, $names) {
                if (isset($config['prepare'])) {
                    $item = call_user_func($config['prepare'], $item, $config);
                }

                if (!($item instanceof SiteContent)) {
                    return false;
                }
                foreach ($tvs as $name => $options) {
                    if ($options['renderer']) {
                        $item->{$name . '_' . 'thumb'} = call_user_func($config['columns'][$name]['renderer'], $item->{$name});
                    };

                    if (isset($item->{$name}) && is_scalar($item->{$name})) {
                        $result = [];
                        $values = array_map('trim', explode('||', $item->{$name}));

                        foreach ($values as $value) {
                            if (isset($options['values'][$value])) {
                                $value = $options['values'][$value];
                            }

                            $result[] = $value;
                        }

                        $item->{$name} = implode(', ', $result);
                    }

                }

                return $item;
            });

        return $items;
    }
    public function getCrumbs(SiteContent $folder, SiteContent $container) {
        if ($container == $folder) {
            return [];
        }

        $parents = [];

        foreach (evo()->getParentIds($folder->id) as $id) {
            $parents[] = $id;

            if ($id == $container->id) {
                break;
            }
        }

        $parents = array_reverse($parents);

        $result = SiteContent::query()
            ->whereIn('id', $parents)
            ->orderByRaw("FIND_IN_SET(id, '" . implode(',', $parents) . "')")
            ->get();

        return $result->push($folder);
    }

    private function getTmplvarsValues(array $names = []) {
        $result = [];

        foreach ($names as $name) {
            $row = SiteTmplvar::where('name', $name)->first();
            $cnf_columns = reset($this->configs)['columns'];

            if (isset($cnf_columns[$name]['renderer'])) {
                $result[$name] = ['renderer' => true];
            }

            if (!empty($row->elements)) {
                $values = [];
                $elements = ParseIntputOptions(ProcessTVCommand($row->elements, '', '', 'tvform', $tv = []));

                if (!empty($elements)) {
                    foreach ($elements as $element) {
                        list($val, $key) = is_array($element) ? $element : explode('==', $element);

                        if (strlen($val) == 0) {
                            $val = $key;
                        }

                        if (strlen($key) == 0) {
                            $key = $val;
                        }

                        $values[$key] = $val;
                    }
                }

                if (!empty($values)) {
                    $result[$name] = [
                        'values' => $values,
                    ];

                    if (in_array($row->type, ['checkbox', 'listbox-multiple'])) {
                        $result[$name]['multiple'] = true;
                    }
                }
            }
        }

        return $result;
    }

    public function actionGetRes($request) {
        $parent = $request->input('id');
        $config = $this->getConfig($request->input('container'));
        $current = SiteContent::find($parent);
        $reslist = $this->getResources($current, $config);
        $reslist = $reslist->toArray();
        return $reslist['data'];
    }

    public function actionUpdate($request) {
        // dd($request->all());
        $data = $request->input('data');
        try {
            DocumentManager::edit($data);
            return 'ok';
        } catch (\EvolutionCMS\Exceptions\ServiceValidationException $exception) {
            $validateErrors = $exception->getValidationErrors(); //Получаем все ошибки валидации
            return $validateErrors; //Выводим все ошибки валидации
        } catch (\EvolutionCMS\Exceptions\ServiceActionException $exception) {
            return $exception->getMessage(); //Выводим ошибку процесса обработки данных
        }

    }

}
