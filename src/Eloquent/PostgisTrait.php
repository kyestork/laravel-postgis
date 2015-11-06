<?php namespace Phaza\LaravelPostgis\Eloquent;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Arr;
use Phaza\LaravelPostgis\Exceptions\PostgisFieldsNotDefinedException;
use Phaza\LaravelPostgis\Geometries\Geometry;
use Phaza\LaravelPostgis\Geometries\GeometryInterface;
use Phaza\LaravelPostgis\Eloquent\Relations\SpatiallyRelatesToOne;
use Phaza\LaravelPostgis\Eloquent\Relations\SpatiallyRelatesToMany;

trait PostgisTrait
{

    public $geometries = [];
    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return \Phaza\LaravelPostgis\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    protected function performInsert(EloquentBuilder $query, array $options = [])
    {
        foreach ($this->attributes as $key => &$value) {
            if ($value instanceof GeometryInterface) {
                $this->geometries[$key] = $value; //Preserve the geometry objects prior to the insert
                $value = $this->getConnection()->raw(sprintf("ST_GeogFromText('%s')", $value->toWKT()));
            }
        }

        $insert = parent::performInsert($query, $options);

        foreach($this->geometries as $key => $value){
            $this->attributes[$key] = $value; //Retrieve the geometry objects so they can be used in the model
        }

        return $insert; //Return the result of the parent insert
    }

    public function setRawAttributes(array $attributes, $sync = false)
    {
        $pgfields = $this->getPostgisFields();

        foreach ($attributes as $attribute => &$value) {
            if (in_array($attribute, $pgfields) && is_string($value) && strlen($value) >= 15) {
                $value = Geometry::fromWKB($value);
            }
        }

        parent::setRawAttributes($attributes, $sync);
    }

    public function getPostgisFields()
    {
        if (property_exists($this, 'postgisFields')) {
            return Arr::isAssoc($this->postgisFields) ? //Is the array associative?
                array_keys($this->postgisFields) : //Returns just the keys to preserve compatibility with previous versions
                $this->postgisFields; //Returns the non-associative array that doesn't define the geometry type.
        } else {
            throw new PostgisFieldsNotDefinedException(__CLASS__ . ' has to define $postgisFields');
        }

    }

    /* TODO: Clean up the redundant methods with some magic */

    public function spatiallyContainsOne($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Contains');
    }

    public function spatiallyContainsMany($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToMany($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Contains');
    }

    public function spatiallyWithinOne($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Within');
    }

    public function spatiallyWithinMany($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToMany($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Within');
    }

    public function spatiallyContainsProperlyOne($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'ContainsProperly');
    }

    public function spatiallyContainsProperlyMany($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToMany($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'ContainsProperly');
    }

    public function spatiallyCoversOne($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Covers');
    }

    public function spatiallyCoversMany($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToMany($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Covers');
    }

    public function spatiallyCrossesOne($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Crosses');
    }

    public function spatiallyCrossesMany($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToMany($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Crosses');
    }

    public function spatiallyIntersectsOne($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Intersects');
    }

    public function spatiallyIntersectsMany($related, $foreignGeometry, $localGeometry)
    {
        $instance = new $related;
        return new SpatiallyRelatesToMany($instance->newQuery(), $this, $instance->getTable().'.'.$foreignGeometry, $localGeometry, 'Intersects');
    }
}
