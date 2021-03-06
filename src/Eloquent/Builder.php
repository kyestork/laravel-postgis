<?php namespace Phaza\LaravelPostgis\Eloquent;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Phaza\LaravelPostgis\Geometries\GeometryInterface;

class Builder extends EloquentBuilder
{
    public function __construct(QueryBuilder $query)
    {
        parent::__construct($query);
    }

    public function update(array $values)
    {
        foreach ($values as $key => &$value) {
            if ($value instanceof GeometryInterface) {
                $value = $this->asWKT($value);
            }
        }

        return parent::update($values);
    }

    protected function getPostgisFields()
    {
        return $this->getModel()->getPostgisFields();
    }


    protected function asWKT(GeometryInterface $geometry)
    {
        return $this->getQuery()->raw(sprintf("ST_GeogFromText('%s')", $geometry->toWKT()));
    }
}
