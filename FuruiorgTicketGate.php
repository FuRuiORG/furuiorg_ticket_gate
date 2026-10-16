<?php
namespace addon\furuiorg_ticket_gate;

use app\common\lib\Plugin;
use app\common\model\HostModel;

/*
 * 工单创建准入拦截(未实名认证 / 账号历史从无产品被激活过)
 * @author FuRuiORG
 * @author YeHuaiJing
 * @time 2026-10-16
 * @copyright Copyright (c) 2026 https://github.com/FuRuiORG/ All rights reserved.
 */
class FuruiorgTicketGate extends Plugin
{
    # 插件基本信息
    public $info = array(
        'name'        => 'FuruiorgTicketGate', //插件英文名,作为插件唯一标识
        'title'       => '工单创建准入拦截',   //插件名称
        'description' => '未实名认证或账号历史从无产品被激活过的用户不允许创建工单', //插件描述
        'author'      => 'FuRuiORG,YeHuaiJing', //开发者
        'version'     => '1.0.0',               //版本号
        'hook_order'  => -1,                    //钩子执行顺序(hook_order 为有符号 int,取 -1 保证本插件监听器在 AppInit 升序排序中排第一,从而拒绝时的 status 能被框架第一个读到)
    );

    # 需要拦截的规则:官方工单插件创建工单
    const TICKET_CREATE_RULE = 'addon\idcsmart_ticket\controller\clientarea\TicketController::create';

    # 插件安装
    public function install()
    {
        # 无建表、无SQL;后台导航由框架自动创建(config.php 提供默认配置)
        return true;
    }

    # 插件卸载
    public function uninstall()
    {
        return true;
    }

    # 前台创建工单准入拦截(钩子:home_check_access)
    # 返回值语义:放行必须返回空数组 [],等价于"本插件不表态",经 array_filter 过滤后完全不影响其它插件;
    # 若放行返回 ['status' => 200] 会短路后续插件(如 idcsmart_sub_account 的子账户权限校验);
    # 拒绝时返回带 status 的非 200 值,因 hook_order = -1 本插件排最前,框架会第一个读到并生效。
    public function homeCheckAccess($param)
    {
        $rule = $param['rule'] ?? '';

        # 仅拦截创建工单,其它规则一律放行
        if ($rule !== self::TICKET_CREATE_RULE) {
            return [];
        }

        # 子账号自动解析为父账号ID,主账号即自身(未登录返回 0)
        $clientId = get_client_id();

        # 未登录交给框架自身登录校验
        if (empty($clientId)) {
            return [];
        }

        $result = $this->checkAllow($clientId);

        return $result['allow'] ? [] : ['status' => 404];
    }

    # 会员中心页面注入前端提示文案改写脚本(钩子:clientarea_view_append)
    public function clientareaViewAppend($param)
    {
        # 子账号自动解析为父账号ID,主账号即自身(未登录返回 0)
        $clientId = get_client_id();

        # 未登录不注入
        if (empty($clientId)) {
            return [];
        }

        $result = $this->checkAllow($clientId);

        # 两者都未通过时优先提示实名文案
        if (!$result['certified']) {
            $msg = lang_plugins('furuiorg_ticket_gate_need_certification');
        } else {
            $msg = lang_plugins('furuiorg_ticket_gate_need_active_host');
        }

        $data = [
            'allow'     => $result['allow'],
            'certified' => $result['certified'],
            'hasHost'   => $result['hasHost'],
            'msg'       => $msg,
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);

        $lines = [
            '<script>',
            'window.__FURUIORG_TICKET_GATE__ = ' . $json . ';',
            '(function () {',
            '  if (!window.Axios || !window.Axios.interceptors || !window.__FURUIORG_TICKET_GATE__) return;',
            '  var gate = window.__FURUIORG_TICKET_GATE__;',
            '  window.Axios.interceptors.response.use(null, function (err) {',
            '    try {',
            '      var url = (err && err.config && err.config.url) || \'\';',
            # 仅在 gate.allow 为 false 时才改写,避免误改其它插件(如子账户权限)造成的 404
            '      if (url.indexOf(\'/ticket\') !== -1 && err.data && err.data.status === 404 && gate.allow === false) {',
            '        err.data.msg = gate.msg;',
            '      }',
            '    } catch (e) {}',
            '    return Promise.reject(err);',
            '  });',
            '})();',
            '</script>',
        ];

        $script = implode("\n", $lines);

        # window.Axios 在页脚脚本中定义,故注入到 body 前
        return ['body' => $script];
    }

    # 准入判定:按后台开关分维度校验(已实名认证 且 历史存在已激活产品)
    private function checkAllow($clientId)
    {
        $config = $this->getConfig();

        $certificationOpen = isset($config['furuiorg_ticket_gate_certification_open']) ? intval($config['furuiorg_ticket_gate_certification_open']) : 1;
        $hostOpen          = isset($config['furuiorg_ticket_gate_host_open'])          ? intval($config['furuiorg_ticket_gate_host_open'])          : 1;

        # 开关关闭时该维度视为通过,不校验
        $certified = $certificationOpen ? (check_certification($clientId) ? true : false) : true;

        $hasHost = true;
        if ($hostOpen) {
            # 未付款/开通中/开通失败/已取消 均不算已激活;不附加 is_delete 条件
            $hasHost = (new HostModel())
                ->where('client_id', $clientId)
                ->whereIn('status', ['Active', 'Suspended', 'Deleted'])
                ->count() > 0;
        }

        return [
            'allow'     => $certified && $hasHost,
            'certified' => $certified,
            'hasHost'   => $hasHost,
        ];
    }
}
