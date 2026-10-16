(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          submitLoading: false,
          configObj: {
            furuiorg_ticket_gate_certification_open: 1,
            furuiorg_ticket_gate_host_open: 1,
          },
        };
      },
      methods: {
        // 获取配置
        getConfig () {
          getTicketGateConfig()
            .then((res) => {
              if (res.data.status !== 200) {
                return;
              }
              const data = res.data.data || {};
              this.configObj.furuiorg_ticket_gate_certification_open = Number(
                data.furuiorg_ticket_gate_certification_open
              )
                ? 1
                : 0;
              this.configObj.furuiorg_ticket_gate_host_open = Number(
                data.furuiorg_ticket_gate_host_open
              )
                ? 1
                : 0;
            })
            .catch(() => {});
        },
        // 保存配置
        async saveConfig () {
          try {
            this.submitLoading = true;
            await setTicketGateConfig({
              furuiorg_ticket_gate_certification_open:
                this.configObj.furuiorg_ticket_gate_certification_open,
              furuiorg_ticket_gate_host_open:
                this.configObj.furuiorg_ticket_gate_host_open,
            });
            this.$message.success(lang.furuiorg_ticket_gate_save_success);
          } catch (error) {
            this.$message.error(lang.furuiorg_ticket_gate_save_fail);
          } finally {
            this.submitLoading = false;
          }
        },
      },
      created () {
        this.getConfig();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
