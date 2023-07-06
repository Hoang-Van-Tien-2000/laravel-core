<?php

namespace App\Http\Controllers\Web;

use App\Repositories\IndustryRepository;
use App\Repositories\InformationRepository;
use App\Repositories\SubsidyRepository;

class HomeController extends WebController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->render();
    }
}
