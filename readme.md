# Build A blog in 30 minutes using Laravel 


In this guide you will learn how to use the power of Laravel, Composer and Open Source to build the admin system for writing blogs in 30 minutes.

We will be using a couple of noteworthy packages to make our job a lot easier, as well as some others which just make the job that little bit easier.

##Step-by-step guide

###First create a new laravel project

    
    composer create-project "laravel/laravel" laravel-sydney
    

###Update the composer.json to include the packages we want

    
    "require": {
        "laravel/framework": "4.2.0",
        "rydurham/sentinel": "1.*"
    },
    "require-dev": {
        "way/generators": "~2.0",
        "fzaninotto/faker": "v1.3.0",
        "theorem/fat-crud": "dev-master"
    },
    

###Update our dependencies

    
    composer update
    

###Configure the Service Providers for the new packages

In app/config/app.php

    
    'Way\Generators\GeneratorsServiceProvider',
    'Sentinel\SentinelServiceProvider',
    'Theorem\FatCrud\FatCrudServiceProvider',
    

###Configure database settings in app/config/local/database.php

Before doing this make sure you have a database setup. If you don't set one up now.

    
    'mysql'     => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'laravel-sydney',
        'username'  => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ],
    

###Configure your environment

In terminal run: 

    
    hostname 
    

You will receive the hostname of your computer which is what is used to identify your environment. Paste the hostname result in bootstrap/start.php (In my case its 'shauns-mbp.fritz.box')

    
    /*
    |--------------------------------------------------------------------------
    | Detect The Application Environment
    |--------------------------------------------------------------------------
    |
    | Laravel takes a dead simple approach to your application environments
    | so you can just specify a machine name for the host that matches a
    | given environment, then we will automatically detect it for you.
    |
    */
    
    $env = $app->detectEnvironment(array(
    
        'local' => array('shauns-mbp.fritz.box'),
    
    ));
    

Now that we have our local environment defined, let's turn on debugging and our URL route to the public folder.

Edit your app/config/local/app.php 

    
    'debug' => true,
    
    'url' => 'http://localhost/laravel-sydney/public/',     // Set this to the URL route to your install of Laravel
    

###Install migration handler in the database

    
    php artisan migrate:install
    

Please Note: If you receive the error "Access denied for user..." you should try the following:

    
    php artisan migrate:install --env=local
    

###Install Authentication - https://github.com/rydurham/Sentinel

The first package we will setup is Sentinel. It's a package written for Laravel to harness to power of Sentury (https://github.com/cartalyst/sentry), which is a well-known "framework agnostic authentication & authorisation system".

This package does all of the heavy lifting for you. It comes with all routes, controllers, view, migrations, seeds, configs. Literally everything that a package can handle for you in Laravel.

Run the migrations:

    
    php artisan migrate --package=rydurham/sentinel
    

Seed the database:

    
    php artisan db:seed --class="SentinelDatabaseSeeder"
    

Publish the package's assets:

    
    php artisan asset:publish rydurham/sentinel
    

Set a "Home" route. This package requires that you have a route named 'home' in your routes.php file so lets add one now:

    
    // Set Home Route
     Route::get('/', array('as' => 'home', function() {
        return View::make('home');
    }));
    

Publish the views:

    
    php artisan view:publish rydurham/sentinel
    

Publish the configuration:

    
    php artisan config:publish rydurham/sentinel
    

We now have an authenitcation system for our application!

###Install Posts - https://github.com/JeffreyWay/Laravel-4-Generators



    
    php artisan generate:scaffold post --fields="title:string, body:text"
    

Seed the data:
Update database/seeds/DatabaseSeeder.php

    
    public function run()
    {
        Eloquent::unguard();
        
        $this->call('PostsTableSeeder');
    }
    

Update database/seeds/PostsTableSeeder.php

    
    Post::create([
        'title' => $faker->name,
        'body'  => $faker->text,
    ]);
    

Seed the Data:

    
    php artisan db:seed
    

Create Views:

    
    php artisan crud:create posts
    

###Load Root URL

If you receive an error with writing to log run the following:

    
    chmod -R 777 app/storage/
    

###Add authentication for accessing posts routes

    
    Route::group([
        'prefix' => 'admin',
        'before' => 'Sentinel\auth'
    ], function() {
        
        Route::resource('posts', 'PostsController', [
            'names' => [
                'index'     => 'posts.index',
                'create'    => 'posts.create',
                'store'     => 'posts.store',
                'show'      => 'posts.show',
                'edit'      => 'posts.edit',
                'update'    => 'posts.update',
                'destroy'   => 'posts.destroy',
            ]
        ]);
    });
    

###Update the controller to define which fields to add to the Model

Update app/controller/PostsController.php

    
    public function store()
    {
        $validator = Validator::make($data = Input::except(['_token']), Post::$rules);
    
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        
        Post::create($data);
        
        return Redirect::route('posts.index');
    }
    

###Update the Post model to allow for mass assignment

Update app/models/Post.php

    
    protected $guarded = [];
    

###Update the config for the Authentication package to /admin/

In app/config/packages/rydurham/sentinel/config.php

    
    'routes' => [
          'users'       => 'admin/users',
          'groups'      => 'admin/groups',
          'sessions'    => 'admin/sessions',
          'login'       => 'admin/login',
          'logout'      => 'admin/logout',
          'register'    => 'admin/register',
          'resend'      => 'admin/resend',
          'forgot'      => 'admin/forgot',
      ],
      

###Copy the layout of the admin package from /app/routes/packages/rydurham/sentinel/layouts/default.blade.php to app/routes/layouts/scaffold.php

###Add posts url to header of app/routes/layouts/scaffold.blade.php

    
    <li {{ (Request::is('*users*') ? 'class="active"' : '') }}><a href="{{ URL::action('Sentinel\UserController@index') }}">Users</a></li>
    <li {{ (Request::is('*groups*') ? 'class="active"' : '') }}><a href="{{ URL::action('Sentinel\GroupController@index') }}">Groups</a></li>
    <li {{ (Request::is('*post*') ? 'class="active"' : '') }}><a href="{{ route('posts.index') }}">Posts</a></li>
    

#Done!

