var fs = require('fs');
var th = th || {};
var cfg_project, cfg_local = null;

exports.projectGetSetting = function(variable) {
  var ret = null;
  if (cfg_project == null) {
    cfg_project = yaml.safeLoad(fs.read('../etc/project.yml'));
  }
  if (fs.exists('../etc/local.yml')) {
    cfg_local = yaml.safeLoad(fs.read('../etc/local.yml'));
  }
  if (cfg_local) {
    ret = eval('cfg_local.' + variable);
  }
  if (!ret) {
    ret = eval('cfg_project.' + variable);
  }
  if (!ret) {
    ret = null;
  }
  return ret;
};
