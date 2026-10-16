// 获取工单创建准入拦截配置
function getTicketGateConfig() {
  return Axios.get(`/furuiorg_ticket_gate/config`);
}
// 保存工单创建准入拦截配置
function setTicketGateConfig(params) {
  return Axios.post(`/furuiorg_ticket_gate/config`, params);
}
