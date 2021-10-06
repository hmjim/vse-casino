var gulp   = require('gulp');
var config = require('../config');

gulp.task('watch', [
    'script:watch',
    'sass:watch',
    'libs:watch'
]);
