# Build a blog using Laravel 


In this guide you will learn how to use the power of Laravel, Composer and Open Source to build the admin system for writing blogs in roughly 30 minutes.

We will be using a couple of noteworthy packages to make our job a lot easier.

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

In terminal run the command below to get the hostname of your computer: 

    
    hostname 
    

The hostname is what is used to identify your environment. Paste the hostname result in bootstrap/start.php (In my case the hostname is 'shauns-mbp.fritz.box').

    
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
    

Now that we have our local environment defined, let's turn on debugging and set our URL route to the public folder.

Edit your app/config/local/app.php 

    
    'debug' => true,
    
    'url' => 'http://localhost/laravel-sydney/public/',     // Set this to the URL route to your install of Laravel
    

###Install migration handler in the database

    
    php artisan migrate:install
    

Please Note: If you receive the error "Access denied for user..." try the following:

    
    php artisan migrate:install --env=local
    

###Install Authentication - https://github.com/rydurham/Sentinel

The first package we will setup is Sentinel. It's a package written for Laravel to harness to power of Sentury (https://github.com/cartalyst/sentry), which is a well-known "framework agnostic authentication & authorisation system".

This package does all of the heavy lifting for you. It comes with all routes, controllers, view, migrations, seeds and configs. Literally everything that a package can offer is used in Sentinel.

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

Jeffrew Way's Generators package allows you to generate the entire boilerplate of code for building basic database models.

    
    php artisan generate:scaffold post --fields="title:string, body:text"
    


####Add generated route

You will receive a success message along with the code for the route for the model. Let's add this to the app/routes.php file:

    
    Route::resource('posts', 'PostsController');
    

####Seed the data

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
    

Seed the data:

    
    php artisan db:seed
    

We now have out database table, controllers and routes covered. Let's move on to the views.

###Create Views - https://github.com/the0rem/FAT-CRUD

Jeffrey Way's Generators package used to handle building views, but the functionality has since been removed. Seeing as this guide would be less impressive if everything wasn't done for you, I've decided to fill the view generating vaccum. Run the command below to create the CRUD pages for your "posts" database table.

    
    php artisan crud:create posts
    

The views have now been created.

###Test homepage

Try to navigate to your homepage. If you receive an error with writing to logs run the following:

    
    chmod -R 777 app/storage/
    

###Add authentication for accessing posts routes

We have the routes for our PostsController defined however we can improve on this. We're going to do a couple of things:
 - Prefix the PostsController urls with '/admin/'
 - Add an authentication filter for accessing anything under '/admin/'
 - Explicitly name the different methods of the PostsController

Update the app/routes.php with the code below:

    
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
      

###Use master view published by Sentinel as our main admin layout

Copy the layout of the admin package from /app/routes/packages/rydurham/sentinel/layouts/default.blade.php to app/routes/layouts/scaffold.php

###Add posts url to header of app/routes/layouts/scaffold.blade.php

    
    <li {{ (Request::is('*users*')  ? 'class="active"' : '') }}><a href="{{ URL::action('Sentinel\UserController@index') }}">Users</a></li>
    <li {{ (Request::is('*groups*') ? 'class="active"' : '') }}><a href="{{ URL::action('Sentinel\GroupController@index') }}">Groups</a></li>
    <li {{ (Request::is('*post*')   ? 'class="active"' : '') }}><a href="{{ route('posts.index') }}">Posts</a></li>
    

###Done!

Congratulations on finishing the blog!

