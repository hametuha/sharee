const gulp = require( 'gulp' );
const fs = require( 'fs' );
const $ = require( 'gulp-load-plugins' )();
const stream = require( 'event-stream' );
const pngquant = require( 'imagemin-pngquant' );


// Sass
gulp.task( 'sass', () => {
	return gulp.src( [
		'./src/scss/**/*.scss'
	] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( $.sourcemaps.init( { loadMaps: true } ) )
		.pipe( $.sassBulkImport() )
		.pipe( $.sass( {
			errLogToConsole: true,
			outputStyle: 'compressed',
			includePaths: [
				'./src/scss'
			]
		} ) )
		.pipe( $.autoprefixer( { browsers: [ 'last 2 version', '> 5%' ] } ) )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( './assets/css' ) );
} );


// Minify All
gulp.task( 'js', () => {
	return gulp.src( [ './src/js/**/*.js' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( $.sourcemaps.init( {
			loadMaps: true
		} ) )
		.pipe( $.babel( {
			presets: [ 'env' ]
		} ) )
		.pipe( $.uglify( {
			output: {
				comments: /^!/
			}
		} ) )
		.on( 'error', $.util.log )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( './assets/js/' ) );
} );


// JS Hint
gulp.task( 'eslint', () => {
	return gulp.src( [ 'src/**/*.js' ] )
		.pipe( $.eslint( { useEslintrc: true } ) )
		.pipe( $.eslint.format() );
} );

// Build modernizr
gulp.task( 'copylib', () => {
	return stream.merge(
		gulp.src( [
			'node_modules/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css',
			'node_modules/select2/dist/css/select2.min.css',
		] )
			.pipe( gulp.dest( 'assets/css' ) ),
		gulp.src( 'node_modules/select2/dist/js/select2.min.js' )
			.pipe( gulp.dest( 'assets/js' ) )
	);
} );

// Image min
gulp.task( 'imagemin', () => {
	return gulp.src( './src/img/**/*' )
		.pipe( $.imagemin( {
			progressive: true,
			svgoPlugins: [ { removeViewBox: false } ],
			use: [ pngquant() ]
		} ) )
		.pipe( gulp.dest( './assets/img' ) );
} );


// watch
gulp.task( 'watch', () => {
	// Make SASS.
	gulp.watch( 'src/scss/**/*.scss', gulp.task( 'sass' ) );
	// JS.
	gulp.watch( [ 'src/js/**/*.js' ], gulp.parallel( 'js', 'eslint' ) );
	// Minify Image.
	gulp.watch( 'src/img/**/*.{jpg,jpeg,gif,png}', gulp.task( 'imagemin' ) );
} );

// Build
gulp.task( 'build', gulp.parallel( 'eslint', 'js', 'sass', 'imagemin' ) );

// Default Tasks
gulp.task( 'default', gulp.task( 'watch' ) );
