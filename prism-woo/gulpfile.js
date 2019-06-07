const gulp = require('gulp');
const browserSync = require('browser-sync');
const sass = require('gulp-sass');
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');
const autoprefixer = require('gulp-autoprefixer');

// Configure browserSync
function browserSyncRun(done) {
  const files = ['./style.css', './*.php'];

  // Initialize Browsersync with a PHP server.
  browserSync.init(files, {
    proxy: 'http://localhost/',
    reloadDelay: 200
  });

  done();
}

// BrowserSync Reload
function browserSyncReload(done) {
  browserSync.reload();
  done();
}

// Configure Sass task to run when specified .scss files change
// Browsersync will also reload browser
function styles() {
  return gulp
    .src('sass/*.scss')
    .pipe(
      sass({
        // outputStyle: 'compressed'
      })
    )
    .pipe(
      autoprefixer({
        cascade: false
      })
    )
    .pipe(gulp.dest('./'))
    .pipe(browserSync.stream());
}

// process JS files and return the stream.
function scripts() {
  return gulp
    .src('js/script.js')
    .pipe(
      babel({
        presets: ['env']
      })
    )
    .pipe(uglify())
    .pipe(gulp.dest('./'))
    .pipe(browserSync.stream());
}

function watchFiles() {
  gulp.watch('sass/**/*.scss', styles);
  gulp.watch('js/script.js', scripts);
}

// define complex tasks
const css = gulp.series(styles);
const js = gulp.series(scripts);
const build = gulp.parallel(styles, scripts);
const watch = gulp.series(
  browserSyncRun,
  gulp.parallel(watchFiles, browserSyncReload)
);

// Default task
exports.build = build;
exports.default = watch;
