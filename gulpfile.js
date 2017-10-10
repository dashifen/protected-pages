var gulp = require("gulp"),
	sourceMaps = require("gulp-sourcemaps"),
	plumber = require("gulp-plumber"),
	minifyJs = require("gulp-uglify"),
	concat = require("gulp-concat"),
	rename = require("gulp-rename");

gulp.task("default", ["watch"]);

gulp.task("watch", function() {
	gulp.watch(["src/Backend/Assets/scripts/**/*.js"], ["js"]);
});

gulp.task("js", function() {
	gulp.src(["src/Backend/Assets/scripts/**/*.js"])
		.pipe(plumber())
		.pipe(sourceMaps.init())
		.pipe(concat("protected-pages.js"))
		.pipe(minifyJs())
		.pipe(sourceMaps.write())
		.pipe(rename({"suffix":".min"}))
		.pipe(gulp.dest("src/Backend/Assets"));
});
