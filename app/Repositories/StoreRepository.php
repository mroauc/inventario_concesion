<?php

namespace App\Repositories;

use App\Models\Store;
use App\Repositories\BaseRepository;

/**
 * Class StoreRepository
 * @package App\Repositories
 * @version April 8, 2023, 3:43 pm UTC
*/

class StoreRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'address'
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
        return Store::class;
    }
}
