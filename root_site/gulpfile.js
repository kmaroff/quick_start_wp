// Подключаем Gulp и все необходимые библиотеки
var gulp           = require('gulp'),
		gutil          = require('gulp-util' ),
		sass           = require('gulp-sass'),
		browserSync    = require('browser-sync'),
		concat         = require('gulp-concat'),
		uglify         = require('gulp-uglify'),
		cleanCSS       = require('gulp-clean-css'),
		rename         = require('gulp-rename'),
		del            = require('del'),
		imagemin       = require('gulp-imagemin'),
		cache          = require('gulp-cache'),
		autoprefixer   = require('gulp-autoprefixer'),
		bourbon        = require('node-bourbon'),
		ftp            = require('vinyl-ftp'),
		notify         = require("gulp-notify");

// Обновление страниц сайта на локальном сервере
gulp.task('browser-sync', function() {
	browserSync({
		proxy: "new_site.loc",
		notify: false
	});
});

// Компиляция main.css
gulp.task('sass', function() {
	return gulp.src('wp-content/themes/new_theme_name/css/style.sass')
		.pipe(sass({
			includePaths: bourbon.includePaths
		}).on('error', sass.logError))
		.pipe(autoprefixer(['last 15 versions']))
		.pipe(concat('style.css'))
  	.pipe(gulp.dest('wp-content/themes/new_theme_name/css/'))
		.pipe(rename({suffix: '.min', prefix : ''}))
		.pipe(cleanCSS())
		.pipe(gulp.dest('wp-content/themes/new_theme_name/css/'))
		.pipe(browserSync.reload({stream: true}))
});

// Скрипты проекта
//gulp.task('scripts', function() {
//	return gulp.src([
//		'wp-content/themes/new_theme_name/libs/modernizr/modernizr.js',
//		'wp-content/themes/new_theme_name/libs/typed/typed.min.js',
//		'wp-content/themes/new_theme_name/libs/likely/likely.js',
//		'wp-content/themes/new_theme_name/libs/plugins-scroll/plugins-scroll.js',
//		'wp-content/themes/new_theme_name/libs/respond/respond.min.js',
//		'wp-content/themes/new_theme_name/libs/zoom/zoom.min.js',
//		'wp-content/themes/new_theme_name/libs/zoom/transition.js',
//		'wp-content/themes/new_theme_name/js/common.js'
//		])
//	.pipe(concat('scripts.js'))
//  .pipe(gulp.dest('wp-content/themes/new_theme_name/js'))
//	.pipe(concat('scripts.min.js'))
//	.pipe(uglify())
//	.pipe(gulp.dest('wp-content/themes/new_theme_name/js'))
//	.pipe(browserSync.reload({stream: true}));
//});

// Сжатие картинок
gulp.task('imagemin', function() {
	return gulp.src('wp-content/themes/new_theme_name/img/**/*')
	.pipe(cache(imagemin()))
	.pipe(gulp.dest('wp-content/themes/new_theme_name/img')); 
});

// Наблюдение за файлами
gulp.task('watch', ['sass', 'browser-sync'], function() {
	gulp.watch('wp-content/themes/new_theme_name/css/style.sass', ['sass']);
	gulp.watch('wp-content/themes/new_theme_name/**/*.php', browserSync.reload);
	gulp.watch('wp-content/themes/new_theme_name/js/**/*.js', browserSync.reload);
	gulp.watch('wp-content/themes/new_theme_name/libs/**/*', browserSync.reload);
});

// Сборка тасков
gulp.task('build', ['sass', 'imagemin']);

// Выгрузка изменений на хостинг
gulp.task('deploy', function() {
	var conn = ftp.create({
		host:      'host',
		user:      'user',
		password:  'password',
		parallel:  10,
		log: gutil.log
	});
	var globs = [
	'wp-content/themes/new_theme_name/**'
	];
	return gulp.src(globs, {buffer: false})
	.pipe(conn.dest('/wp-content/themes/new_theme_name'));
});
gulp.task('clearcache', function () { return cache.clearAll(); });
gulp.task('default', ['watch']);