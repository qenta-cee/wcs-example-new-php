var gulp = require('gulp');
var clean = require('gulp-clean');
var cucumber = require('cucumber');
var composer = require('gulp-composer');

var argv = require('yargs').argv;

gulp.task('copy-bootstrap', ['cleanup'], function () {
    gulp.src('./bower_components/bootstrap/dist/css/bootstrap.min.css')
        .pipe(gulp.dest('./public/css/'));

    gulp.src('./bower_components/bootstrap/dist/fonts/*')
        .pipe(gulp.dest('./public/fonts/'));

    gulp.src('./bower_components/bootstrap/dist/js/bootstrap.min.js')
        .pipe(gulp.dest('./public/js/'));

    gulp.src('./bower_components/bootstrap-side-navbar/source/assets/stylesheets/navbar-fixed-side.css')
        .pipe(gulp.dest('./public/css/'));
});

gulp.task('copy-jquery', ['cleanup'], function () {
    gulp.src('./bower_components/jquery/dist/jquery.min.js')
        .pipe(gulp.dest('./public/js/'));
});

gulp.task('copy-app-assets', ['cleanup'], function () {
    gulp.src('./assets/js/*.js')
        .pipe(gulp.dest('./public/js/'));

    gulp.src('./assets/css/*.css')
        .pipe(gulp.dest('./public/css/'));
});

gulp.task('cleanup', function () {
    return gulp.src(['./public/css', './public/js', './public/fonts'], {read: false})
        .pipe(clean());
});

gulp.task('composer-cleanup', function () {
    return gulp.src(['./vendor'], {read: false})
        .pipe(clean());
});

gulp.task('composer-install', ['composer-cleanup'], function () {
    return composer('install', {'no-dev': true});
});

gulp.task('copy-dist', ['default', 'composer-install'], function () {
    return gulp.src(['index.php', './config/**/*', './public/**/*', './public/.*', './src/**/*', './view/**/*', './vendor/**/*', './results', 'LICENSE'], {
        base: './'
    }).pipe(gulp.dest('dist'));
});

gulp.task('guitest', function(callback) {
    var command = ['node', 'cucumber-js'];
    command.push('--format');
    command.push('json:reports/cucumber.json');
    var worldParameters = {};
    if(argv.browser) {
        worldParameters.browser = argv.browser;
    }

    if(argv.seleniumHost) {
        worldParameters.seleniumHost = argv.seleniumHost;
    }

    if(argv.baseUri) {
        worldParameters.baseUri = argv.baseUri;
    }

    var ngrok = require('ngrok');

    var port = argv.port | 8080;
    return ngrok.connect(port, function(err, url) {
        if(err) {
            throw new Error('Error starting up ngrok: ' + err);
        }
        worldParameters.externalBaseUri = url;
        command.push("--world-parameters");
        command.push(JSON.stringify(worldParameters));
        command.push('features');
        return cucumber.Cli(command).run(function(result) {
            console.log(result);
            ngrok.disconnect(url);
            process.exit(0);
        });
    });
});

gulp.task('default', ['cleanup', 'copy-bootstrap', 'copy-jquery', 'copy-app-assets']);
gulp.task('install', ['copy-bootstrap', 'copy-jquery', 'copy-app-assets']);
gulp.task('dist', ['default', 'composer-cleanup', 'composer-install', 'copy-dist']);