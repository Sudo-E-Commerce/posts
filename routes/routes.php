<?php
App::booted(function() {
	$namespace = 'Sudo\Post\Http\Controllers';
	
	Route::namespace($namespace)->name('admin.')->prefix(config('app.admin_dir'))->middleware(['web', 'auth-admin'])->group(function() {
		// Bài viết
		Route::resource('posts', 'PostController');
		// Danh mục bài viết
		Route::resource('post_categories', 'PostCategoryController');
	});
});