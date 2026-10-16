<?php
namespace addon\furuiorg_ticket_gate\controller;

use addon\furuiorg_ticket_gate\FuruiorgTicketGate;
use app\admin\model\PluginModel;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 工单创建准入拦截(后台设置)
 * @desc 工单创建准入拦截(后台设置)
 * @use addon\furuiorg_ticket_gate\controller\ConfigController
 */
class ConfigController extends PluginAdminBaseController
{
    /**
     * 时间 2026-10-16
     * @title 设置
     * @desc 设置
     * @author YeHuaiJing
     * @version v1
     * @url /admin/v1/furuiorg_ticket_gate/config
     * @method GET
     * @return int furuiorg_ticket_gate_certification_open - desc:是否开启实名认证限制,1开启,0关闭
     * @return int furuiorg_ticket_gate_host_open - desc:是否开启已激活产品限制,1开启,0关闭
     */
    public function getConfig()
    {
        $plugin = new FuruiorgTicketGate();

        # 默认配置(config.php)与数据库已保存值合并
        $config = array_merge($plugin->getDefaultConfig(), $plugin->getConfig());

        $data = [
            'furuiorg_ticket_gate_certification_open' => isset($config['furuiorg_ticket_gate_certification_open']) ? intval($config['furuiorg_ticket_gate_certification_open']) : 1,
            'furuiorg_ticket_gate_host_open'          => isset($config['furuiorg_ticket_gate_host_open']) ? intval($config['furuiorg_ticket_gate_host_open']) : 1,
        ];

        return json([
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data,
        ]);
    }

    /**
     * 时间 2026-10-16
     * @title 设置
     * @desc 设置
     * @author YeHuaiJing
     * @version v1
     * @url /admin/v1/furuiorg_ticket_gate/config
     * @method POST
     * @param int furuiorg_ticket_gate_certification_open - desc:是否开启实名认证限制,1开启,0关闭 validate:optional
     * @param int furuiorg_ticket_gate_host_open - desc:是否开启已激活产品限制,1开启,0关闭 validate:optional
     */
    public function setConfig()
    {
        $param = $this->request->only([
            'furuiorg_ticket_gate_certification_open',
            'furuiorg_ticket_gate_host_open',
        ]);

        $PluginModel = new PluginModel();

        $plugin = $PluginModel->where('name', 'FuruiorgTicketGate')->find();

        if (empty($plugin)) {
            return json(['status' => 400, 'msg' => lang_plugins('fail_message')]);
        }

        $config = json_decode($plugin['config'], true) ?: [];

        # 每个开关统一规范为 0 或 1;未提交的键保留原值(默认 1)
        $config['furuiorg_ticket_gate_certification_open'] = isset($param['furuiorg_ticket_gate_certification_open'])
            ? (intval($param['furuiorg_ticket_gate_certification_open']) ? 1 : 0)
            : (isset($config['furuiorg_ticket_gate_certification_open']) ? intval($config['furuiorg_ticket_gate_certification_open']) : 1);

        $config['furuiorg_ticket_gate_host_open'] = isset($param['furuiorg_ticket_gate_host_open'])
            ? (intval($param['furuiorg_ticket_gate_host_open']) ? 1 : 0)
            : (isset($config['furuiorg_ticket_gate_host_open']) ? intval($config['furuiorg_ticket_gate_host_open']) : 1);

        $plugin->save([
            'config' => json_encode($config)
        ]);

        return json([
            'status' => 200,
            'msg' => lang_plugins('success_message'),
        ]);
    }
}
