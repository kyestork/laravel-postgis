<?php namespace Phaza\LaravelPostgis\Eloquent\Relations;

use Illuminate\Database\Eloquent\Relations\Relation;

abstract class SpatialRelation extends Relation {

	/**
     * The foreign key of the parent model.
     *
     * @var string
     */
    protected $foreignGeometry;

    /**
     * The local key of the parent model.
     *
     * @var string
     */
    protected $localGeometry;

    public $allowedComparisons = [
        '3DDFullyWithin',
        '3DDWithin',
        '3DIntersects',
        'Contains',
        'ContainsProperly',
        'CoveredBy',
        'Covers',
        'Crosses',
        'DFullyWithin',
        'DWithin',
        'Equals',
        'Intersects',
        'OrderingEquals',
        'Overlaps',
        'Touches',
        'Within'
    ];

}