<?php

namespace App\Repositories;

use App\Repositories\Traits\CustomQuery;
use Core\Repositories\BaseRepository;

class CustomRepository extends BaseRepository
{
    use CustomQuery;
    public function __construct()
    {
        parent::__construct();
        $this->init($this->getModel()->getTable());
    }

    public function getListForIndex($params)
    {
        $params['sort'] = empty($params['sort']) ? 'id' : $params['sort'];
        $params['direction'] = empty($params['direction']) ? 'desc' : $params['direction'];
        request()->merge($params);

        return $this->search($params)->paginate(getConfig('page_number.' . $this->getModel()->getTable(), getConfig('page_number')));
    }

    public function getListForExport($params, $fields = ['*'])
    {
        $sortField = !empty($params['sort']) ? $params['sort'] : 'id';
        $sortType = !empty($params['direction']) ? $params['direction'] : 'desc';

        $builder = $this->buildSelectForExport($fields);
        $this->buildConditionForExport($builder, $params);
        return $builder->orderBy($sortField, $sortType)->get();
    }

    protected function buildSelectForExport($fields)
    {
        return $this->select($fields);
    }

    protected function buildConditionForExport(&$builder, $params = [])
    {
        //
    }
}
