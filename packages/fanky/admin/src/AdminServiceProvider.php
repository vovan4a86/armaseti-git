<?php namespace Fanky\Admin;

use Illuminate\Support\ServiceProvider;
use Collective\Html\FormBuilder;

class AdminServiceProvider extends ServiceProvider {

	/**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // регистрируем namespace для файлов представлений
        $this->loadViewsFrom(__DIR__.'/views', 'admin');

        require __DIR__.'/routes.php';
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
		FormBuilder::component('groupText', 'admin::components.form_block.text', ['name', 'value' => null, 'label' => null, 'attributes' => []]);
		FormBuilder::component('groupDate', 'admin::components.form_block.date', ['name', 'value' => null, 'label' => null, 'attributes' => []]);
		FormBuilder::component('groupRichtext', 'admin::components.form_block.rich_text', ['name', 'value' => null, 'label' => null, 'attributes' => []]);
		FormBuilder::component('groupTextarea', 'admin::components.form_block.textarea', ['name', 'value' => null, 'label' => null, 'attributes' => []]);
		FormBuilder::component('groupNumber', 'admin::components.form_block.number', ['name', 'value' => null, 'label' => null, 'attributes' => []]);
		FormBuilder::component('groupSelect', 'admin::components.form_block.select', ['name', 'list' => null, 'value' => null, 'label' => null, 'attributes' => []]);
		FormBuilder::component('groupCheckbox', 'admin::components.form_block.checkbox', ['name', 'value' => null, 'checked' => null, 'label' => null, 'attributes' => []]);
    }

}
