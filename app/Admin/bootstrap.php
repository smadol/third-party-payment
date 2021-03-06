<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Encore\Admin\Form::forget(['map', 'editor']);
Admin::js('/js/admin/splitmode/edit.js');
Admin::css('/vendor/layui/css/layui.css');
Admin::js('/vendor/layui/layui.js');
//Admin::js('/vendor/layui/lay/modules/layer.js');
Admin::js('/js/admin/platuser/add.js');
Admin::js('/js/admin/platuser/profile.js');
Admin::js('/js/admin/group/payment.js');


