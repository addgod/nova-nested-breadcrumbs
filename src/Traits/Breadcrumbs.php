<?php

namespace Addgod\NestedBreadcrumbs\Traits;

use Illuminate\Support\Collection;
use Laravel\Nova\Resource;

trait Breadcrumbs
{
    public static function breadcrumbs()
    {
        return true;
    }

    public static function breadcrumbResourceLabel()
    {
        return static::label();
    }

    public function breadcrumbResourceTitle()
    {
        return $this->title();
    }

    /**
     * Get the parent to be displayed in the breadcrumbs.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function breadcrumbParent()
    {
    }

    /**
     * Prepare the resource for JSON serialization using the given fields.
     *
     * @param \Illuminate\Support\Collection $fields
     *
     * @return array
     */
    protected function serializeWithId(Collection $fields)
    {
        $parent = parent::serializeWithId($fields);

        return array_merge($parent, [
            'label' => $this->breadcrumbResourceLabel(),
            'title' => $this->breadcrumbResourceTitle(),
        ]);
    }
}
