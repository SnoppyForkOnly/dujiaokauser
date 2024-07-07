<?php

namespace App\Admin\Repositories;

use App\Models\ArticleCategory as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ArticleCategory extends EloquentRepository
{

    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;

}
