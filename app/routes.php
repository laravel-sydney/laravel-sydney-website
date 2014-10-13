<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Set Home Route
Route::get('/', array('as' => 'home', function()
{
	return View::make('hello');
}));


Route::group([
	'prefix' => 'admin',
	'before' => 'Sentinel\auth'
], function() {

	Route::resource('posts', 'Admin\PostsController',  [
		'names' => [
			'index'   => 'posts.index',
			'create'  => 'posts.create',
			'store'   => 'posts.store',
			'show'    => 'posts.show',
			'edit'    => 'posts.edit',
			'update'  => 'posts.update',
			'destroy' => 'posts.destroy',
		]
	]);

});

