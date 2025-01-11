<?php

namespace EvolutionCMS\Resedittable\Controllers;

use EvolutionCMS\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class Controller {

    public function show(ResEditTable $resEdit, SiteContent $container, SiteContent $folder = null) {
        if ($container->id) {
            $config = $resEdit->getConfig($container->id);
            $current = $folder ?? $container;
            // $items = $resEdit->getResources($current, $config);
            $crumbs = $resEdit->getCrumbs($current, $container);

            return view('resedittable::list', [
                'container' => $container,
                'folder' => $folder,
                'crumbs' => $crumbs,

                'config' => $config,
                'lang' => $config['lang'],
            ]);
        }
    }

    public function action(Request $request, ResEditTable $resEditTable) {
        $action = $request->input('action');
        $selected = $request->input('selected');
        $docId = $request->input('id');
        // dd($request->all());

        if (is_string($action)) {
            $action = 'action' . ucfirst($action);
            $res = call_user_func([$resEditTable, $action], $request);
            return response()->json($res);
            // return $resEditTable->actionGetRes($parent);
        }

        return response()->json(['status' => 'failed']);
    }

}
