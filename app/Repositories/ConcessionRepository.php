<?php

namespace App\Repositories;

use App\Models\Concession;
use App\Repositories\BaseRepository;

/**
 * Class ConcessionRepository
 * @package App\Repositories
 * @version April 7, 2023, 9:53 pm UTC
*/

class ConcessionRepository extends BaseRepository
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
        return Concession::class;
    }
}
