var util = require('gulp-util');

var production = util.env.production || util.env.prod || false;
var destPath = 'assets';

var config = {
    env       : 'development',
    production: production,

    src: {
        root         : 'app',
        sass         : 'app/sass',
        js           : 'app/js',
        libs         : 'app/libs',
    },
    dest: {
        root : destPath,
        css  : destPath + '/css',
        js   : destPath + '/js',
    },

    setEnv: function(env) {
        if (typeof env !== 'string') return;
        this.env = env;
        this.production = env === 'production';
        process.env.NODE_ENV = env;
    },

    logEnv: function() {
        util.log(
            'Environment:',
            util.colors.white.bgRed(' ' + process.env.NODE_ENV + ' ')
        );
    },

};

config.setEnv(production ? 'production' : 'development');

module.exports = config;
