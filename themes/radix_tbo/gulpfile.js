// Include gulp.
var gulp = require('gulp');
var browserSync = require('browser-sync').create();
var config = require('./config.json');

// Include plugins.
var sass = require('gulp-sass');
var imagemin = require('gulp-imagemin');
var pngcrush = require('imagemin-pngcrush');
var shell = require('gulp-shell');
var plumber = require('gulp-plumber');
var notify = require('gulp-notify');
var autoprefix = require('gulp-autoprefixer');
var glob = require('gulp-sass-glob');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
//var cssclean = require('gulp-clean-css');
var sourcemaps = require('gulp-sourcemaps');

// CSS.
gulp.task('css', function() {
  return gulp.src(config.css.src)
    .pipe(glob())
    .pipe(plumber({
      errorHandler: function (error) {
        notify.onError({
          title:    "Gulp",
          subtitle: "Failure!",
          message:  "Error: <%= error.message %>",
          sound:    "Beep"
        }) (error);
        this.emit('end');
      }}))
    .pipe(sourcemaps.init())
    .pipe(sass({
      style: 'compressed',
      errLogToConsole: true,
      includePaths: config.css.includePaths
    }))
    .pipe(autoprefix('last 2 versions', '> 1%', 'ie 9', 'ie 10'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(config.css.dest))
    .pipe(browserSync.reload({ stream: true, match: '**/*.css' }));
});

// Compress images.
gulp.task('images', function () {
  return gulp.src(config.images.src)
    .pipe(imagemin({
      progressive: true,
      svgoPlugins: [{ removeViewBox: false }],
      use: [pngcrush()]
    }))
    .pipe(gulp.dest(config.images.dest));
});

// Fonts.
gulp.task('fonts', function() {
  return gulp.src(config.fonts.src)
    .pipe(gulp.dest(config.fonts.dest));
});

// Watch task.
gulp.task('watch', function() {
  gulp.watch(config.css.src, ['css']);
  gulp.watch(config.images.src, ['images']);
});

// Static Server + Watch
gulp.task('serve', ['css', 'fonts', 'watch'], function() {
  browserSync.init({
      proxy: 'http://tigob2bselfcare.co.dev/',
      open: false
  });
  browserSync.reload()
});

// Run drush to clear the theme registry.
gulp.task('drush', shell.task([
  'drush cache-clear theme-registry'
]));

// Default Task
gulp.task('default', ['serve']);


//JS - Vendor JS
//Execute after including new js libraries: gulp vendorjs
var vendor_list_js = ["node_modules/swiper/dist/js/swiper.js","node_modules/jscrollpane/script/jquery.mousewheel.js",
    "node_modules/jscrollpane/script/jquery.jscrollpane.js","node_modules/jquery-validation/dist/jquery.validate.js"]
gulp.task('vendorjs', function() {
  return gulp.src(vendor_list_js)
    .pipe(concat('vendor_scripts.js'))
    .pipe(gulp.dest('assets/js'))
    .pipe(rename('vendor_scripts.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('assets/js'));
});

//CSS - Vendor CCS
//Execute after including new js libraries: gulp vendorcss
var vendor_list_css = ["node_modules/swiper/dist/css/swiper.css", "node_modules/jscrollpane/style/jquery.jscrollpane.css"]
gulp.task('vendorcss', function() {
  return gulp.src(vendor_list_css)
    .pipe(concat('vendor_styles.css'))
    .pipe(gulp.dest('assets/css'))
    .pipe(rename('vendor_styles.min.css'))
    //.pipe(cssclean())
    .pipe(gulp.dest('assets/css'));
});

