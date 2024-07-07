<?php

namespace App\Admin\Repositories;

use App\Models\InviteUser as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class InviteUser extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
