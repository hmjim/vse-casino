var gulp = require('gulp');
var config = require('../config.js');

gulp.task('script:js', function () {
    return gulp
        .src(config.src.js + '/*.js')
        .pipe(gulp.dest(config.dest.js));
});

gulp.task('script', [
    'script:js'
]);

gulp.task('script:watch', function () {
    gulp.watch(config.src.js + '/*', ['script']);
});