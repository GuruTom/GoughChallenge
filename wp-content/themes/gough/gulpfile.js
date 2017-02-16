var gulp = require('gulp');
var plumber = require('gulp-plumber');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var livereload = require('gulp-livereload');
var minifycss = require('gulp-minify-css');


function handleError(err) {
  console.log(err.toString());
  this.emit('end');
}


gulp.task('css', function() {
 
	return gulp.src('scss/screen.scss')
		.pipe(plumber({ errorHandler: handleError }))
	    .pipe(sass())
	    .pipe(autoprefixer('last 10 version'))
	    //.pipe(minifycss())
	    .pipe(gulp.dest('css/'))

});


// Watch for changes

gulp.task('watch', function() {
	gulp.watch('scss/*.scss', ['css']);

	livereload.listen();
	gulp.watch(['css/*.css']).on('change', livereload.changed);
});




// Default Task

gulp.task('default', ['css', 'watch']);