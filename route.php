<?php
/**
 * 工单创建准入拦截 - 后台自定义路由
 * @author FuRuiORG
 * @author YeHuaiJing
 * @time 2026-10-16
 * @copyright Copyright (c) 2026 https://github.com/FuRuiORG/ All rights reserved.
 */
use think\facade\Route;

# 后台
Route::group(DIR_ADMIN . '/v1', function () {
    # 设置
    Route::get('furuiorg_ticket_gate/config', "\\addon\\furuiorg_ticket_gate\\controller\\ConfigController@getConfig")
        ->append(['_plugin' => 'furuiorg_ticket_gate', '_controller' => 'config', '_action' => 'get_config']);
    # 设置
    Route::post('furuiorg_ticket_gate/config', "\\addon\\furuiorg_ticket_gate\\controller\\ConfigController@setConfig")
        ->append(['_plugin' => 'furuiorg_ticket_gate', '_controller' => 'config', '_action' => 'set_config']);
})
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckAdmin::class);
