<?php

namespace App\Http\Controllers\Web;

use Core\Http\Controllers\BaseController;

class WebController extends BaseController
{
    protected $formDataKeySuffix;

    protected $metaDescription;

    public function __construct()
    {
        parent::__construct();
        $this->setTitle(__('messages.page_title.web.'. getControllerName()));
        $this->setMetaDescription(__('messages.meta_description.web.default'));
    }

    /**
     * @param $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function render($view = null, array $data = [], array $mergeData = [])
    {
        $data['metaDescription'] = $this->getMetaDescription();
        return parent::render($view, $data, $mergeData);
    }

    /**
     * @return string
     */
    protected function setFormDataKeySuffix($formDataKeySuffix = null)
    {
        $this->formDataKeySuffix = $formDataKeySuffix;
    }

    protected function getFormData($suffix = null, bool $clean = false)
    {
        $this->setFormDataKeySuffix($suffix);
        $data = session()->get($this->getFormDataKey(), []);

        if ($clean) {
            $this->cleanFormData($data);
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function getFormDataKey()
    {
        return getArea() . '_' . getControllerName() . '_' . $this->formDataKeySuffix;
    }

    /**
     * @param array $data
     */
    protected function cleanFormData(array $data = [])
    {
        session()->put([$this->getFormDataKey() => []]);
        session()->flash('hasClean', !empty($data));
    }

    /**
     * @param $data
     * @return $this
     */
    protected function setFormData($data)
    {
        $this->setFormDataKeySuffix(data_get($data, 'id'));
        session()->put([$this->getFormDataKey() => $data]);

        return $this;
    }
}
