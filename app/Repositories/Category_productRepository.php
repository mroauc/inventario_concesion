<?php

namespace App\Repositories;

use App\Models\Category_product;
use App\Repositories\BaseRepository;

/**
 * Class Category_productRepository
 * @package App\Repositories
 * @version April 8, 2023, 1:19 am UTC
*/

class Category_productRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Category_product::class;
    }
}
