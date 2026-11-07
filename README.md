# furuiorg_ticket_gate

魔方业务V10的工单创建准入插件，限制未实名或从未激活产品的用户创建工单。

## 规则

满足以下全部条件才允许创建工单：

1. 已完成实名认证；
2. 账号历史存在`Active`、`Suspended`或`Deleted`状态的产品。

两项规则可在插件配置中单独关闭。

## 安装

把插件放到：

```
public/plugins/addon/furuiorg_ticket_gate/
```

在后台启用。默认两项限制均开启。

## 配置

| 配置 | 说明 | 默认 |
| --- | --- | --- |
| `furuiorg_ticket_gate_certification_open` | 实名认证限制 | 1 |
| `furuiorg_ticket_gate_host_open` | 已激活产品限制 | 1 |

## 已知问题

子账号可通过缓存权限绕过`home_check_access`钩子。可禁用子账号功能，或修改`idcsmart_ticket`插件。

## License

MIT，见`LICENSE`。
