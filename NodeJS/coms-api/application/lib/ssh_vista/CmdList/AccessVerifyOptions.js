var config = require('../config');
var OptionsCmds = [
	config.AuthenticationCmds[0],
	config.AuthenticationCmds[1],
    { "prompt" : config.options.namespace, "cmd" : "D ^ZU", "ignoreAndWait" : true, fcn : null, "LookingForString" : false, "String2Find" : "" },
    { "prompt" : "ACCESS CODE: ", "cmd" : "1nurse", "ignoreAndWait" : true, fcn : null, "LookingForString" : false, "String2Find" : "" },
    { "prompt" : "VERIFY CODE: ", "cmd" : "nurse1", "ignoreAndWait" : false, fcn : null, "LookingForString" : false, "String2Find" : "" },
    { "prompt" : "Option: ", "cmd" : "", "ignoreAndWait" : false, fcn : null, "LookingForString" : false, "String2Find" : "" }
];

exports.OptionsCmds = OptionsCmds;
