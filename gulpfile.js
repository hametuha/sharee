var gulp        = require('gulp'),
    fs          = require('fs'),
    $           = require('gulp-load-plugins')(),
    stream      = require('event-stream'),
    pngquant    = require('imagemin-pngquant');


// Sass
gulp.task('sass', function () {
  return gulp.src([
    './src/scss/**/*.scss'
  ])
    .pipe($.plumber({
      errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.sourcemaps.init({loadMaps: true}))
    .pipe($.sassBulkImport())
    .pipe($.sass({
      errLogToConsole: true,
      outputStyle    : 'compressed',
      includePaths   : [
        './src/scss'
      ]
    }))
    .pipe($.autoprefixer({browsers: ['last 2 version', '> 5%']}))
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest('./assets/css'));
});


// Minify All
gulp.task('js', function () {
  return gulp.src(['./src/js/**/*.js'])
    .pipe($.plumber({
      errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.sourcemaps.init({
      loadMaps: true
    }))
    .pipe($.babel({
      presets: ['env']
    }))
	  .pipe($.uglify({
		  output: {
			  comments: /^!/
		  }
	  }))
    .on('error', $.util.log)
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest('./assets/js/'));
});


// JS Hint
gulp.task('eslint', function () {
  return gulp.src(['src/**/*.js'])
    .pipe($.eslint({ useEslintrc: true }))
    .pipe($.eslint.format());
});

// Build modernizr
gulp.task('copylib', function () {
  return stream.merge(
    gulp.src([
      'node_modules/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css',
      'node_modules/select2/dist/css/select2.min.css',
    ])
      .pipe(gulp.dest('assets/css')),
    gulp.src('node_modules/select2/dist/js/select2.min.js')
      .pipe(gulp.dest('assets/js'))
  );
});

// Image min
gulp.task('imagemin', function () {
  return gulp.src('./src/img/**/*')
    .pipe($.imagemin({
      progressive: true,
      svgoPlugins: [{removeViewBox: false}],
      use        : [pngquant()]
    }))
    .pipe(gulp.dest('./assets/img'));
});


// watch
gulp.task('watch', function () {
  // Make SASS
  gulp.watch('src/scss/**/*.scss', ['sass']);
  // JS
  gulp.watch(['src/js/**/*.js'], ['js', 'eslint']);
  // Minify Image
  gulp.watch('src/img/**/*', ['imagemin']);
});

// Build
gulp.task('build', ['eslint', 'js', 'sass', 'imagemin']);

// Default Tasks
gulp.task('default', ['watch']);
