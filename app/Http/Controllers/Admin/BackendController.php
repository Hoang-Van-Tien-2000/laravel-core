<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\CustomRepository;
use App\Services\CustomService;
use App\Validators\CustomValidator;
use Core\Http\Controllers\BaseController;
use Core\Providers\Facades\Storages\BaseStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;

class BackendController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /** @var CustomRepository $repository */
    protected $repository;

    /** @var CustomValidator $validator */
    protected $validator;

    /** @var CustomService $service */
    protected $service;

    protected $formDataKeySuffix;

    protected $csvFilename = '';

    protected $csvHeaders = [];

    /**
     * @return string
     */
    protected function setFormDataKeySuffix($formDataKeySuffix = null)
    {
        $this->formDataKeySuffix = $formDataKeySuffix;
    }

    /**
     * @return string
     */
    protected function getFormDataKey()
    {
        return getArea() . '_' . getControllerName() . (!empty($this->formDataKeySuffix) ? '_' . $this->formDataKeySuffix : '');
    }

    /**
     * @param $data
     * @return $this
     */
    protected function setFormData($data)
    {
        $primaryKey = $this->repository->getKeyName();
        $this->setFormDataKeySuffix(data_get($data, $primaryKey));
        session()->put([$this->getFormDataKey() => $data]);

        return $this;
    }

    /**
     * @param null $suffix
     * @param bool $clean
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getFormData($suffix = null, bool $clean = false)
    {
        $this->setFormDataKeySuffix($suffix);
        $data = session()->get($this->getFormDataKey(), []);

        if ($clean) {
            $this->cleanFormData();
        }

        return $data;
    }

    protected function cleanFormData()
    {
        session()->forget($this->getFormDataKey());
    }

    public function index()
    {
        $data = request()->all();
        $record = $this->service->index($data);

        $this->setViewData(['records' => $record,]);

        if (request('page') > $record->lastPage()) {
            if (session()->has('action_success')) {
                session()->flash('action_success', session()->get('action_success'));
            }

            if (session()->has('action_failed')) {
                session()->flash('action_failed', session()->get('action_failed'));
            }

            return redirect()->route(request()->route()->getName(), array_merge($data, ['page' => $record->lastPage()]));
        }

        return $this->render();
    }

    public function create()
    {
        $formData = $this->getFormData('', true);
        $this->setViewData(['record' => $formData]);

        return $this->render();
    }

    public function edit($id)
    {
        $validate = $this->validator->validateShow($id);

        if (!$validate) {
            session()->flash('action_failed', __('messages.no_result_found'));

            return redirect(getBackUrl());
        }

        $formData = $this->getFormData($id, true);
        $record = !empty($formData) ? $formData : $this->repository->find($id)->toArray();

        $this->setViewData(['record' => $record]);

        return $this->render();
    }

    public function show($id)
    {
        $validate = $this->validator->validateShow($id);

        if (!$validate) {
            session()->flash('action_failed', __('messages.no_result_found'));

            return redirect(getBackUrl());
        }

        $record = $this->repository->find($id)->toArray();
        $this->setViewData(['record' => $record]);

        return $this->render();
    }

    public function valid()
    {
        $params = request()->all();
        $this->prepareValid($params);
        $this->setFormData($params);

        $primaryKey = $this->repository->getKeyName();
        $primaryValue = data_get($params, $primaryKey);

        $validate = !empty($primaryValue) ? $this->validator->validateUpdate($params) : $this->validator->validateCreate($params);

        if (!$validate) {
            return redirect()->back()->withErrors($this->validator->errorsBag())->withInput();
        }

        $routeConfirm = str_replace('.valid', '', request()->route()->getName()) . '.confirm';

        return redirect()->route($routeConfirm, [$primaryKey => $primaryValue]);
    }

    public function store()
    {
        $routeIndex = str_replace('.store', '', request()->route()->getName()) . '.index';

        try {
            $params = $this->getFormData();

            if (empty($params)) {
                return redirect()->route($routeIndex);
            }

            if (!$this->validator->validateCreate($params)) {
                $this->cleanFormData();
                session()->flash('action_failed', $this->validator->errorsBag()->first());

                return redirect()->route($routeIndex);
            }

            if (!$this->service->store($params)) {
                $this->cleanFormData();
                session()->flash('action_failed', __('messages.create_failed'));

                return redirect()->route($routeIndex);
            }

            session()->flash('action_success', __('messages.create_success'));
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            session()->flash('action_failed', __('messages.create_failed'));
        }

        $this->cleanFormData();

        return redirect()->route($routeIndex);
    }

    public function update($id)
    {
        $routeIndex = str_replace('.update', '', request()->route()->getName()) . '.index';
        $params = $this->getFormData($id);

        try {
            if (empty($id) || empty($params)) {
                return redirect()->route($routeIndex);
            }

            if (!$this->validator->validateShow($id)) {
                session()->flash('action_failed', __('messages.no_result_found'));

                return redirect()->route($routeIndex);
            }

            if (!$this->validator->validateUpdate($params)) {
                $this->cleanFormData();
                session()->flash('action_failed', $this->validator->errorsBag()->first());

                return redirect()->route($routeIndex);
            }

            if (!$this->service->update($id, $params)) {
                $this->cleanFormData();
                session()->flash('action_failed', __('messages.update_failed'));

                return redirect()->route($routeIndex);
            }

            session()->flash('action_success', __('messages.update_success'));
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            session()->flash('action_failed', __('messages.update_failed'));
        }

        $this->cleanFormData();

        return redirect()->route($routeIndex);
    }

    public function destroy($id)
    {
        try {
            if (empty($id)) {
                return redirect(getBackUrl());
            }

            $validate = $this->validator->validateShow($id);

            if (!$validate) {
                session()->flash('action_failed', __('messages.no_result_found'));

                return redirect(getBackUrl());
            }

            if (!$this->service->destroy($id)) {
                session()->flash('action_failed', __('messages.delete_failed'));

                return redirect(getBackUrl());
            }

            session()->flash('action_success', __('messages.delete_success'));
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            session()->flash('action_failed', __('messages.delete_failed'));
        }

        return redirect(getBackUrl());
    }

    public function downloadCsv()
    {
        $this->service->downloadCsv(request()->all(), $this->csvFilename, $this->csvHeaders);
    }

    /**
     * @param $params
     */
    protected function hasUploadFile(&$params)
    {
        foreach ($params as $key => $item) {
            if ($item instanceof UploadedFile) {
                $file = BaseStorage::uploadToTmp($item);
                if (!empty($file)) {
                    $params[$key] = $file;
                    $params['is_upload'][] = $key;
                }
            }
        }
    }

    protected function prepareValid(&$params)
    {
    }

    protected function prepareBeforeStore(&$data)
    {
    }

    protected function prepareBeforeUpdate(&$data)
    {
    }
}
