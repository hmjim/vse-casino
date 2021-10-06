var gulp = require('gulp');
var rename = require('gulp-rename');
var config = require('../config.js');

gulp.task('libs:js', function () {
    return gulp
        .src(config.src.libs + '/**/*.js')
        .pipe(rename({dirname: ''}))
        .pipe(gulp.dest(config.dest.js));
});

gulp.task('libs:css', function () {
    return gulp
        .src(config.src.libs + '/**/*.css')
        .pipe(rename({dirname: ''}))
        .pipe(gulp.dest(config.dest.css));
});

gulp.task('libs', [
    'libs:js',
    'libs:css'
]);

gulp.task('libs:watch', function () {
    gulp.watch(config.src.libs + '/**/*', ['libs']);
});


