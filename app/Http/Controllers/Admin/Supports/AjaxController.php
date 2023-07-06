<?php

namespace App\Http\Controllers\Admin\Supports;

use App\Http\Controllers\Admin\BackendController;
use Illuminate\Support\MessageBag;

class AjaxController extends BackendController
{
    public function valid()
    {
        $params = request()->all();
        $this->prepareValid($params);
        $this->setFormData($params);

        $primaryKey = $this->repository->getKeyName();
        $primaryValue = data_get($params, $primaryKey);

        $validate = empty($primaryValue) ? $this->validator->validateCreate($params) : $this->validator->validateUpdate($params);

        if ($validate) {
            return $this->renderJson([
                'success' => true,
            ]);
        }

        return $this->renderJson([
            'success' => false,
            'messages' => new MessageBag($this->validator->errorsBag()->messages()),
        ]);
    }

    public function store()
    {
        try {
            $params = $this->getFormData();

            if (empty($params)) {
                return $this->renderJson([
                    'success' => false,
                    'message' => __('messages.no_result_found'),
                ]);
            }

            if (!$this->validator->validateCreate($params)) {
                $this->cleanFormData();

                return $this->renderJson([
                    'success' => false,
                    'message' => $this->validator->errorsBag()->first(),
                ]);
            }

            if (!$this->service->store($params)) {
                $this->cleanFormData();

                return $this->renderJson([
                    'success' => false,
                    'message' => __('messages.create_failed'),
                ]);
            }

            $this->cleanFormData();
            session()->flash('action_success', __('messages.create_success'));

            return $this->renderJson([
                'success' => true,
                'message' => __('messages.create_success'),
            ]);
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        $this->cleanFormData();

        return $this->renderJson([
            'success' => false,
            'message' => __('messages.create_failed'),
        ]);
    }

    public function edit($id)
    {
        $validate = $this->validator->validateShow($id);

        if (!$validate) {
            return $this->renderJson([
                'success' => false,
                'message' => __('messages.no_result_found'),
            ]);
        }

        return $this->renderJson([
            'success' => true,
            'data' => $this->repository->find($id),
        ]);
    }

    public function update($id)
    {
        $params = $this->getFormData($id);

        try {
            if (empty($id) || empty($params)) {
                return $this->renderJson([
                    'success' => false,
                    'message' => __('messages.no_result_found'),
                ]);
            }

            if (!$this->validator->validateShow($id)) {
                return $this->renderJson([
                    'success' => false,
                    'message' => __('messages.no_result_found'),
                ]);
            }

            if (!$this->validator->validateUpdate($params)) {
                $this->cleanFormData();

                return $this->renderJson([
                    'success' => false,
                    'message' => $this->validator->errorsBag()->first(),
                ]);
            }

            if (!$this->service->update($id, $params)) {
                $this->cleanFormData();

                return $this->renderJson([
                    'success' => false,
                    'message' => __('messages.update_failed'),
                ]);
            }

            $this->cleanFormData();
            session()->flash('action_success', __('messages.update_success'));

            return $this->renderJson([
                'success' => true,
                'message' => __('messages.update_success'),
            ]);
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        $this->cleanFormData();

        return $this->renderJson([
            'success' => false,
            'message' => __('messages.update_failed'),
        ]);
    }
}
