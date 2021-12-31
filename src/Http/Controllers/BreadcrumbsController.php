<?php

namespace Addgod\NestedBreadcrumbs\Http\Controllers;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\InteractsWithLenses;
use Laravel\Nova\Http\Requests\InteractsWithResources;
use Laravel\Nova\Nova;
use Throwable;

class BreadcrumbsController extends Controller
{
    use InteractsWithLenses;
    use InteractsWithResources;

    protected $resourceClass;
    protected $resource;
    protected $crumbs;

    public function __construct()
    {
        $this->crumbs = new Collection();
    }

    public function __invoke(Request $request)
    {
        $view = str_replace('-', ' ', Str::after($request->get('view'), 'custom-'));
        $novaHome = Str::finish($request->get('location')['origin'] . Nova::path(), '/');
        $path = Str::after($request->get('location')['href'], $novaHome);
        $pathParts = collect(explode('/', $path))->filter();

        if ($pathParts->has(1)) {
            $this->resourceClass = Nova::resourceForKey($pathParts->get(1));
        }

        if ($pathParts->has(2)) {
            try {
                $this->resource = $this->findResourceOrFail($pathParts->get(2));
            } catch (Throwable $e) {
                // silence.
            }
        }

        if ($pathParts->has(3) && $view != 'lens') {
            $title = sprintf('%s - %s', $this->resourceClass::singularLabel(), __(Str::title($view)));
            $this->appendToCrumbs($title, $pathParts->slice(0, 4)->implode('/'));
        }

        if ($this->resource && $this->resourceClass && $view == 'detail') {
            $title = sprintf(
                '%s - %s',
                $this->resourceClass::singularLabel(),
                $this->resource->breadcrumbResourceTitle()
            );
            $this->appendToCrumbs($title, $pathParts->slice(0, 3)->implode('/'));
        }

        if ($view == 'create') {
            $title = sprintf('%s - %s', $this->resourceClass::singularLabel(), __(Str::title($view)));
            $this->appendToCrumbs($title, $pathParts->slice(0, 3)->implode('/'));
        } elseif ($view == 'dashboard.custom') {
            $this->appendToCrumbs(__(Str::title($request->get('name'))), $pathParts->slice(0, 3)->implode('/'));
        } elseif ($view == 'lens') {
            $lens = Str::title(str_replace('-', ' ', $pathParts->get(3)));
            $this->appendToCrumbs($lens, $pathParts->slice(0, 4)->implode('/'));
        }

        if ($request->has('query')) {
            $query = collect($request->get('query'))->filter();

            if ($query->count() > 1) {
                $cloneParts = clone $pathParts;
                $cloneParts->put(1, $query->get('viaResource'));
                $cloneParts->put(2, $query->get('viaResourceId'));
                $this->resourceClass = Nova::resourceForKey($query->get('viaResource'));
                $this->resource = $this->findResourceOrFail($query->get('viaResourceId'));
                $this->appendToCrumbs($this->resource->breadcrumbResourceTitle(), $cloneParts->slice(0, 3)->implode('/'));
            }
        }

        if ($this->resourceClass && $this->resource) {
            while ($this->resource->breadcrumbParent()) {
                $this->resource = Nova::newResourceFromModel($this->resource->breadcrumbParent());
                $this->resourceClass = \get_class($this->resource);
                $this->appendParentToCrumbs($this->resource);
            }
        }

        if ($this->resourceClass) {
            $this->appendToCrumbs(
                $this->resourceClass::breadcrumbResourceLabel(),
                'resources/' . $this->resourceClass::uriKey()
            );
        }

        $this->appendToCrumbs(__('Home'), '/');

        $this->crumbs = $this->crumbs->reverse()->values();

        return $this->getCrumbs();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCrumbs(): Collection
    {
        $last = $this->crumbs->pop();
        $last['path'] = null;
        $this->crumbs->push($last);

        return $this->crumbs;
    }

    /**
     * Get the class name of the resource being requested.
     *
     * @return mixed
     */
    public function resource()
    {
        return tap($this->resourceClass, function ($resource) {
            abort_if(\is_null($resource), 404);
        });
    }

    protected function appendParentToCrumbs(Resource $resource)
    {
        $class = \get_class($resource);
        if (!$class::breadcrumbs()) {
            return;
        }
        $path = 'resources';
        $resourcePath = $path . '/' . $class::uriKey() . '/' . $resource->model()->id;

        $this->appendToCrumbs($resource->breadcrumbResourceTitle(), $resourcePath);
    }

    protected function appendToCrumbs($title, $url = null)
    {
        $this->crumbs->push([
            'title' => $title,
            'path'  => Str::start($url, '/'),
        ]);
    }
}
