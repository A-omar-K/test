
<Configuration>
[
    Determining The Current Environment
    [
        $environment = Illuminate\Support\Facades\App::environment();
        $environment = Illuminate\Support\Facades\App::environment(['local', 'omar']); // True or False
    ],

    Hiding Environment Variables From Debug Pages
    [
        you may do this by updating / creating debug_blacklist
        option in your config/app.php configuration file.
        NOTE: some variables are available in both the environment variables
        and the server / request data. Therefore, you may need to blacklist them
        for both $_ENV and $_SERVER.
        Add this to config/app.php
        'debug_blacklist' => [
            '_ENV' => [
                'APP_KEY',
                'DB_PASSWORD',
            ],

            '_SERVER' => [
                'APP_KEY',
                'DB_PASSWORD',
            ],

            '_POST' => [
                'DB_PASSWORD',
            ],
        ],
    ],

    Accessing Configuration Values
    [
        get
        [
            $value = config('app.name');
        ],

        set
        [
            config([
                'app.name' => 'App Name',
            ]);
        ],
    ],

    Configuration Caching
    [
        To give your application a speed boost you should cache all
        of your configuration files into a single file using the
        "php artisan config:cache" and do not run it during local
        development.

        NOTE: If you execute the config:cache command during your deployment process,
        you should be sure that you are only calling the env function from within your configuration files.
        Once the configuration has been cached, the .env file will not be loaded and all calls to
        the env function will return null.
    ],

    Maintenance Mode
    [
        enable
        [
            php artisan down --message="This is the message" --allow=127.0.0.1 --allow=192.0.0.1
        ],

        disable
        [
            php artisan up
        ],

        customize template
        [
            resources/views/errors/503.blade.php
        ],
    ],

]
</Configuration>

<architectureConcepts>

    <requestLisfecycle>
        <introduction>
            The goal of this document is to give you a
            good, high-level overview of how the Laravel
            framework works.
        </introduction>

        <lifecycleOverview>
            <firstThings>
                The entry  point for all requests to a Laravel
                application  is the public/index.php file. All
                requests are directed to this file by your web
                server configuration. The index.php file loads
                the  Composer generated autoloader definition,
                and  then retrieves an instance of the Laravel
                application     from bootstrap/app.php script.
            </firstThings>

            <HTTP_ConsoleKernels>
                The    incoming   request is sent to either the
                HTTP  kernel or   the console kernel, depending
                on   the type of   request that is entering the
                application.     These two kernels serve as the
                central location that all requests flow through

                The HTTP     kernel also defines a list of HTTP
                middleware  that all requests must pass through
                before        being handled by the application.

                These middleware handle reading and writing the
                HTTP session, determining if the application is
                in  maintenance mode, verifying the CSRF token.
                Think    of the kernel as being a big black box
                that   represents your entire application. Feed
                it  HTTP requests and will return HTTP response
            </HTTP_ConsoleKernels>

            <serviceProviders>
                All of the service provider for the application
                are configured      in the config/app.php file,
                providers array.     First, the register method
                will be called on all providers, then, once all
                providers have been registered, the boot method
                will be called.
            </serviceProviders>

            <dispatchRequest>
                Once  the application has been bootstrapped and
                all service providers have been registered, the
                Request    will be handed off to the router for
                dispatching.       The router will dispatch the
                request to a    route or controller, as well as
                run any route specific middleware.

            </dispatchRequest>

        </lifecycleOverview>

        <focusOnServiceProvider>
            Service          providers are truly the key to
            bootstrapping        a Laravel application. The
            application instance    is created, the service
            providers are    registered, and the request is
            handed      to the bootstrapped application. By
            default, the AppServiceProvider is fairly empty
            This provider      is a great place to add your
            application's     own bootstrapping and service
            container bindings.
        </focusOnServiceProvider>
    </requestLisfecycle>

    <serviceContainer>

        <introduction>
            The Laravel     service container is a powerful
            tool for  managing class dependencies and means
            this:    class dependencies are "injected" into
            the class via the constructor or  in some cases
            "setter"         methods. Look at this example:
            App\Http\Controllers\UserController.php In this
            example,   the UserController needs to retrieve
            users  from a data source. So, we will inject a
            service that is able to retrieve users. In this
            context,    our UserRepository most likely uses
            Eloquent to retrieve user information from  the
            database.
        </introduction>

        <binding>

            <bindingBasics>
                <NOTE>
                    There is no need to bind classes into the container
                    if        they do not depend on any interfaces. The
                    container  does not need to be instructed on how to
                    build these     objects, since it can automatically
                    resolve these objects using reflection.
                </NOTE>

                <simpleBindings>
                    Within a service provider, you always have access
                    to the container via the $this->app property. We
                    can    register a binding using the bind method,
                    passing the class or interface name that we wish
                    to register along with a Closure that returns an
                    instance of the class:
                    <!--
                        $this->app->bind('HelpSpot\API', function ($app) {
                            return new \HelpSpot\API($app->make('HttpClient'));
                        });
                    -->
                    Note that we receive the container itself as an
                    argument   to the resolver. We can then use the
                    container    to resolve sub-dependencies of the
                    object we are building.
                </simpleBindings>

                <bindingASingleton>
                    The singleton method binds a class or interface
                    into the container that should only be resolved
                    one time. Once a singleton binding is resolved,
                    the same object instance will be returned on
                    subsequent calls into the container:
                    <!--
                        $this->app->singleton('HelpSpot\API', function ($app) {
                            return new \HelpSpot\API($app->make('HttpClient');
                        });
                    -->
                </bindingASingleton>

                <bindingInstances>
                    You may also bind an existing object instance
                    into the container using the instance method.
                    The given instance will always be returned on
                    subsequent calls into the container:
                    <!--
                        $api = new \HelpSpot\API\(new HttpClient);

                        $this->app->instance('HelpSpot\API', $api);
                    -->
                </bindingInstances>

                <bindingPrimitives>
                    Sometimes you may have a class that receives some
                    injected classes, but also needs an injected
                    primitive value such as an integer You may easily
                    user contextual binding to inject any value your
                    class may need:
                    <!--
                        $this->app->when('App\Http\Controllers\UserController')
                                    ->needs('$variableName')
                                    ->give($value);
                    -->
                </bindingPrimitives>

            </bindingBasics>

            <bindingInterfacesToImplementations>
                A  very powerful feature of the service container
                is  its   ability to bind an interface to a given
                implementation. For example, let's assume we have
                an   EventPusher interface and a RedisEventPusher
                implementation.            Once we have coded our
                RedisEventPusher implementation of this interface
                we can register it with the service container
                like so:

                <!--
                    $this->app->bind(
                        'App\Contracts\EventPusher',
                        'App\Services\RedisEventPusher'
                    );
                -->
                This   statement tells the container that it should
                inject   the RedisEventPusher when a class needs an
                implementation of EventPusher. Now we can type-hint
                the EventPusher  interface in a constructor, or any
                other location   where dependencies are injected by
                the service container:
                <!--
                    public function __construct(EventPusher $pusher)
                    {
                        $this->pusher = $pusher;
                    }
                -->
            </bindingInterfacesToImplementations>

            <contextualBinding>
                Sometimes you may have two classes that utilize the
                same interface, but you wish to inject different
                implementation into each class. For example, two
                controllers may depend on different implementations
                of the Illuminate\Contracts\Filesystem\Filesystem
                contract. Laravel provides a simple, fluent interface
                for defining this behavior:

                <!--
                    use App\Http\Controllers\PhotoController;
                    use App\Http\Controllers\UploadController;
                    use App\Http\Controllers\VideoController;
                    use Illuminate\Contracts\Filesystem\Filesystem;
                    use Illuminate\Support\Facades\Storage;

                    $this->app->when(PhotoController::class)
                              ->needs(Filesystem::class)
                              ->give(function () {
                                    return Storage::disk('local');
                              });

                    $this->app->when([VideoController::class, UploadController::class])
                              ->needs(Filesystem::class)
                              ->give(function () {
                                    return Storage::disk('s3');
                              });

                -->

            </contextualBinding>

            <tagging>
                Occasionally,      you may need to resolve all of a
                certain "category" of binding. For example, perhaps
                you are building  a report aggregator that receives
                an array of many         different Report interface
                implementations. After       registering the Report
                implementations,    you can assign them a tag using
                the tag method:

                <!--
                    $this->app->bind('SpeedReport', function() {});
                    $this->app->bind('MemoryReport', function() {});

                    $this->app>tag([
                        'SpeedReport',
                        'MemoryReport'
                    ], 'reports');
                -->
                Once the services have been tagged, you may
                easily resolve them all via the tagged method:
                <!--
                    $this->app->bind('ReportAggregator', function() {
                        return new ReportAggregator($app->tag('reports');
                    });
                -->
            </tagging>

            <extendingBindings>
                The extend method allows the modification of resolved
                services. For example, when a service is resolved you
                may run additional code to decorate or configure the
                service. The extend method accepts a Closure, which
                should return the modified service, as its only
                argument. The Closure receives the service being
                resolved and the container instance:
                <!--
                    $this->app->extend(Service::class, function ($service, $app) {
                        return new DecoratedService($service);
                    });
                -->
            </extendingBindings>

        </binding>

        <resolving>
            <theMakeMthod>
                You may use the make meethod to resolve a class
                instance out of the container. The make method
                accepts the name of the class or interface you
                wish to resolve:
                <!--
                    $api = $this->app->make('HelpSpot\API');
                -->
                If you are in a location of your code that does
                not have access to the $app variable, you may
                use the global resolve helper:
                <!--
                    $api = resolve('HelpSpot\API');
                -->
                If some of your class's dependencies are not resolvable
                via the container, you may inject them by passing them
                as an associative array into the makeWith method:
                <!--
                    $api = $this->app->makeWith('HelpSpot\API', ['id' => 1]);
                -->
            </theMakeMthod>

            <automaticInjection>
                Alternatively, and importantly, you may "type-hint"
                the dependency in the constructor of a class that is
                resolved by the container, including controllers
                event listeners, middleware, and more. additionally,
                you may type-hint dependencies in the handle method of
                queued jobs. In practice, this is how most of your
                objects should be resolved by the container.
                For example, you may type-hint a repository defined
                by your application in a controller's constructor.
                The repository will automatically be resolved and
                injected into the class:
                <!--
                    namespace App\Http\Controllers;

                    use App\Users\Repository as UserRepository;

                    class UserController extends Controller
                    {
                        /**
                         * The user repository instance.
                         */
                        protected $users;

                        /**
                         * Create a new controller instance.
                         *
                         * @param  UserRepository  $users
                         * @return void
                         */
                        public function __construct(UserRepository $users)
                        {
                            $this->users = $users;
                        }

                        /**
                         * Show the user with the given ID.
                         *
                         * @param  int  $id
                         * @return Response
                         */
                        public function show($id)
                        {
                            //
                        }
                    }
                -->
            </automaticInjection>
        </resolving>

        <containerEvents>
            The service container fires an event each time
            it resolves an object. You may listen to this
            event using the resolving method:
            <!--
                $this->app->resolving(\HelpSpot\API::class, function ($api, $app) {
                    // Called when container resolves objects of type "HelpSpot\API"
                });
            -->
            As you can see, the object being resolved will be
            passed to the callback, allowing you to set any
            additional properties on the object before it is
            given to its consumer.
        </containerEvents>
        <PSR-11>
            Laravel's service container implements the PSR-11
            interface. Therefore, you may type-hint the PSR-11
            container interface to obtain an instance of the
            Laravel container:
            <!--
                use Psr\Container\ContainerInterface;

                Route::get('/', function (ContainerInterface $container) {
                    $service = $container->get('Service');
                });
            -->
            An exception is thrown                if the given identifier can't be
            resolved. The exception                         will be an instance of
            Psr\Container\NotFoundExceptionInterface             if the identifier
            was never bound.             If the identifier was bound but was unable
            to be resolved, an instance of Psr\Container\ContainerExceptionInterface
            will be thrown.
        </PSR-11>

    </serviceContainer>

    <serviceProvider>
        <introduction>
            Service  providers are the central place of all Laravel application
            bootstrapping.  But, what do we mean by "bootstrapped"? In general,
            we mean registering things, including registering service container
            bindings,     event listeners, middleware, and even routes. Service
            providers   are the central place to configure your application. if
            you   open the config/app.php file, you will see a providers array.
            These   are all of teh service provider classes that will be loaded
            for your        application. Note that many of these are "deferred"
            providers,    meaning they will not be loaded on every request, but
            only when the services they provide are actually needed.
        </introduction>

        <writingServiceProviders>
            <theRegisterMethod>
                All service providers extend the Illuminate\Support\ServiceProvider
                class. Most service providers   contain a register and boot method.
                Within the register method, you    should only bind things into the
                service   singleton  container. You should never attempt to register attempt
                to register      any event listeners, routes, or any other piece of
                functionality.
                <NOTE>
                    You always have access to the $app property which provides
                    access to the service container.
                </NOTE>

                <!--
                    namespace App\Providers;

                    use Illuminate\Support\ServiceProvider;
                    use Riak\Connection;

                    class RiakServiceProvider extends ServiceProvider
                    {
                        /**
                         * Register any application services.
                         *
                         * @return void
                         */
                        public function register()
                        {
                            $this->app->singleton(Connection::class, function ($app) {
                                return new Connection(config('riak'));
                            });
                        }
                    }
                -->

                This service provider only defines a register method, and uses
                that method to define an implementation of Riak\Connection in
                the service container.

                The bindings and singletons Properties: if your service   provider
                registers many simple bindings, you may wish to use   the bindings
                and singletons properties instead of manually     registering each
                container binding. When the service provider is      loaded by the
                framework, it will automatically check for these properties    and
                register their bindings:

                <!--
                    namespace App\Providers;

                    use App\Contracts\DowntimeNotifier;
                    use App\Contracts\ServerProvider;
                    use App\Services\DigitalOceanServerProvider;
                    use App\Services\PingdomDowntimeNotifier;
                    use App\Services\ServerToolsProvider;
                    use Illuminate\Support\ServiceProvider;

                    class AppServiceProvider extends ServiceProvider
                    {
                        /**
                         * All of the container bindings that should be registered.
                         *
                         * @var array
                         */
                        public $bindings = [
                            ServerProvider::class => DigitalOceanServerProvider::class,
                        ];

                        /**
                         * All of the container singletons that should be registered.
                         *
                         * @var array
                         */
                        public $singletons = [
                            DowntimeNotifier::class => PingdomDowntimeNotifier::class,
                            ServerToolsProvider::class => ServerToolsProvider::class,
                        ];
                    }
                -->
            </theRegisterMethod>

            <theBootMethod>
                So, what if we need to register a view composer within
                our service provider? This   should be done within the
                boot method. This method is     called after all other
                service providers have been    registered, meaning you
                have access to all other       services that have been
                registered by the framework:

                <!--
                    namespace App\Providers;

                    use Illuminate\Support\ServiceProvider;

                    class ComposerServiceProvider extends ServiceProvider
                    {
                        /**
                         * Bootstrap any application services.
                         *
                         * @return void
                         */
                        public function boot()
                        {
                            view()->composer('view', function () {
                                //
                            });
                        }
                    }
                -->

                Boot Method Dependency Injection
                You may type-hint dependencies for your service provider's
                boot method. The service container will automatically
                inject any dependencies you need:
                <!--
                    use Illuminate\Contracts\Routing\ResponseFactory;

                    public function boot(ResponseFactory $response)
                    {
                        $response->macro('caps', function ($value) {
                            //
                        });
                    }
                -->

            </theBootMethod>
        </writingServiceProviders>

        <registeringProvider>
            you can list the class names of your service providers
            in config/app.php file inside providers array.
        </registeringProvider>

        <deferredProviders>
            If your provider   is only registering     bindings in the
            service container, you may choose to defer its registration
            until one of the registered bindings    is actually needed.
            Deferring the loading of such a provider   will improve the
            performance of your application since it is not loaded from
            the filesystem on every request.

            To defer the loading of a provider,          implement the
            \Illuminate\Contracts\support\DeferrableProvider interface
            and define a provides method. The provides   method should
            return the service container bindings    registered by the
            provider:

            <!--
                namespace App\Providers;

                use Illuminate\Contracts\Support\DeferrableProvider;
                use Illuminate\Support\ServiceProvider;
                use Riak\Connection;

                class RiakServiceProvider extends ServiceProvider implements DeferrableProvider
                {
                    /**
                     * Register any application services.
                     *
                     * @return void
                     */
                    public function register()
                    {
                        $this->app->singleton(Connection::class, function ($app) {
                            return new Connection($app['config']['riak']);
                        });
                    }

                    /**
                     * Get the services provided by the provider.
                     *
                     * @return array
                     */
                    public function provides()
                    {
                        return [Connection::class];
                    }
                }
            -->
        </deferredProviders>
    </serviceProvider>

    <facades>
        <introduction>
            Facades provide a "static" interface to classes that are available
            in the    application's service container. Laravel ships with many
            facades  which provide access to almost all of Laravel's features.
            Laravel    facades serve as "static proxies" to underlying classes
            in the service         container, providing the benefit of a terse
            expressive           syntax while maintaining more testability and
            flexibility than      traditional static methods. All of laravel's
            facades are defined in   the Illuminate\Support\Facades namespace.
            so we can easily access a facade like so:
            <!--
                use Illuminate\Support\Facades\Cache;

                Route::get('/cache', function() {
                    return Cache::get('key');
                });
            -->
        </introduction>

        <whenToUserFacades>
            Facades provide  a terse, memorable syntax that allows you to use
            Laravel's features without remembering long class names that must
            be injected or configured manually. Furthermore, because of their
            unique     usage of PHP's dynamic methods, they are easy to test.
            When using     facades, pay special attention to the size of your
            class so that its scope of responsibility stays narrow.

            <NOTE>
                When building a third-party package that interacts with  Laravel,
                it's better to inject Laravel contracts instead of using facades.
                Since packages are built outside of Laravel itself, you will  not
                have access to Laravel's facade testing helpers.
            </NOTE>

            <facadesVsDependencyInjection>
                One of the primary benefits of   dependency injection is the ability
                to swap implementations of the injected class. This is useful during
                testing since you can inject a  mock or stub and assert that various
                methods were called on the stub. Typically, it would not be possible
                to mock or stub a truly static class method. However, since  facades
                use dynamic methods to proxy method calls to objects resolved   from
                the service container, we actually can test facades just as we would
                test an injected class instance. For example, given the    following
                route:
                <!--
                    use Illuminate\Support\Facades\Cache;

                    Route::get('/cache', function () {
                        return Cache::get('key');
                    });
                -->

                We can write the following test to verify that the
                Cache::get method was called with the argument  we
                expected:

                <!--
                    use Illuminate\Support\Facades\Cache;

                    /**
                     * A basic functional test example.
                     *
                     * @return void
                     */
                    public function testBasicExample()
                    {
                        Cache::shouldReceive('get')
                             ->with('key')
                             ->andReturn('value');

                        $this->visit('/cache')
                             ->see('value');
                    }
                -->
            </facadesVsDependencyInjection>

            <facadesVsHelpersFunctions>
                In addition to facades, Laravel includes a variety of "helper"
                functions which can perform common tasks like generating views
                firing events, dispatching jobs, or sending HTTP    responses.
                Many of these helper functions perform the same  function as a
                corresponding facade. For example, this facade call and helper
                call are equivalent:

                <!--
                    return View::make('profile');

                    return view('profile');
                -->

                There is absolutely no practical difference between facades
                and helper functions. When using helper functions, you  may
                still test them exactly as you would the      corresponding
                facade. For example, given the following route:

                <!--
                    Route::get('/cache', function () {
                        return cache('key');
                    });
                -->
                Under the hood, the cache helper is going to call get method
                on the class underlying the Cache facade. So, even though we
                are using the helper function, we can write the    following
                test to verify that the method was called with the  argument
                we expected:

                <!--
                    use Illuminate\Support\Facades\Cache;

                    /**
                     * A basic functional test example.
                     *
                     * @return void
                     */
                    public function testBasicExample()
                    {
                        Cache::shouldReceive('get')
                             ->with('key')
                             ->andReturn('value');

                        $this->visit('/cache')
                             ->see('value');
                    }
                -->
            </facadesVsHelpersFunctions>
        </whenToUserFacades>

        <howFacadesWork>
            In a Laravel application, a facade is a class that provides  access
            to an object from the container. The machinery that makes this work
            is the Facade class. Laravel's facades, and any custom facades  you
            create will extend the base Illuminate\Support\Facades\Facade class
            The Facade base class makes use of the __callStatic()  magic-method
            to defer calls from your facade to an object resolved      from the
            container. In the example below a call is made to the Laravel cache
            system. By glancing at this code, one might assume that  the static
            method get is being called on the Cache class:
            <!--

                namespace App\Http\Controllers;

                use App\Http\Controllers\Controller;
                use Illuminate\Support\Facades\Cache;

                class UserController extends Controller
                {
                    /**
                     * Show the profile for the given user.
                     *
                     * @param  int  $id
                     * @return Response
                     */
                    public function showProfile($id)
                    {
                        $user = Cache::get('user:'.$id);

                        return view('profile', ['user' => $user]);
                    }
                }
            -->

            Notice that near the top of the file we are "importing"  the Cache
            facade. This facade serves as a proxy to accessing the  underlying
            implementation of the Illuminate\Contracts\Cache\Factory interface
            Any calls we make using the facade will be passe to the underlying
            instance of Laravel's cache service. If we look at that      class
            Illuminate\Support\Facade\Cache you'll see that there is no static
            method get:

            <!--
                class Cache extends Facade
                {
                    /**
                     * Get the registered name of the component.
                     *
                     * @return string
                     */
                    protected static function getFacadeAccessor() { return 'cache'; }
                }
            -->

            Instead, the Cache facade extends  the base Facade class and defines
            the method getFacadeAccessor().  This method's job is to return  the
            name of a service container  binding. When a user references     any
            static method on the Cache facade Laravel resolves the cache binding
            from the service container and runs the requested method (in    this
            case, get) against that object.
        </howFacadesWork>
        <realTimeFacades>
            Using real-time facades, you may treat any class in your application
            as if it were a facade. To illustrate how this can be used,    let's
            examine an alternative. For example, let's assume our  Podcast model
            has a publish method. However, in order to publish the podcast,   we
            need to inject a Publisher instance:
            <!--

                namespace App;

                use App\Contracts\Publisher;
                use Illuminate\Database\Eloquent\Model;

                class Podcast extends Model
                {
                    /**
                     * Publish the podcast.
                     *
                     * @param  Publisher  $publisher
                     * @return void
                     */
                    public function publish(Publisher $publisher)
                    {
                        $this->update(['publishing' => now()]);

                        $publisher->publish($this);
                    }
                }
            -->
            Injecting a publisher implementation into the method allows    us
            to easily test the method in isolation since we can mock      the
            injected publisher. However, it requires us to always      pass a
            publisher instance each time we call the publish    method. Using
            real-time facades, we can maintain the same    testability  while
            not being required to explicitly pass a Publisher   instance.  To
            generate a real-time facade, prefix the namespace of the imported
            class with Facades:

            <!--
                namespace App;

                use Facades\App\Contracts\Publisher;
                use Illuminate\Database\Eloquent\Model;

                class Podcast extends Model
                {
                    /**
                     * Publish the podcast.
                     *
                     * @return void
                     */
                    public function publish()
                    {
                        $this->update(['publishing' => now()]);

                        Publisher::publish($this);
                    }
                }
            -->
            When the real-time facade is used, the publisher   implementation
            will be resolved out of the service container using   the portion
            of the interface or class name that appears after   the    Facades
            prefix. When testing, we can use Laravel's built-in facade testing
            helpers to mock this method call:

            <!--

                namespace Tests\Feature;

                use App\Podcast;
                use Facades\App\Contracts\Publisher;
                use Illuminate\Foundation\Testing\RefreshDatabase;
                use Tests\TestCase;

                class PodcastTest extends TestCase
                {
                    use RefreshDatabase;

                    /**
                     * A test example.
                     *
                     * @return void
                     */
                    public function test_podcast_can_be_published()
                    {
                        $podcast = factory(Podcast::class)->create();

                        Publisher::shouldReceive('publish')->once()->with($podcast);

                        $podcast->publish();
                    }
                }
            -->
        </realTimeFacades>
        <facadeClassReference>
            Below you will find every facade and its underlying class.
            The service  container binding key is also included where
            applicable.

            Facade	                        Class	                                ServiceContainerBinding
                App	                    Illuminate\Foundation\Application	            app
                Artisan	                Illuminate\Contracts\Console\Kernel	            artisan
                Auth	                Illuminate\Auth\AuthManager	                    auth
                Auth (Instance)	        Illuminate\Contracts\Auth\Guard	                auth.driver
                Blade	                Illuminate\View\Compilers\BladeCompiler	        blade.compiler
                Broadcast	            Illuminate\Contracts\Broadcasting\Factory
                Broadcast(Instance)	    Illuminate\Contracts\Broadcasting\Broadcaster
                Bus	                    Illuminate\Contracts\Bus\Dispatcher
                Cache	                Illuminate\Cache\CacheManager	                cache
                Cache (Instance)	    Illuminate\Cache\Repository	                    cache.store
                Config	                Illuminate\Config\Repository	                config
                Cookie	                Illuminate\Cookie\CookieJar	                    cookie
                Crypt	                Illuminate\Encryption\Encrypter	                encrypter
                DB	                    Illuminate\Database\DatabaseManager	            db
                DB (Instance)	        Illuminate\Database\Connection	                db.connection
                Event	                Illuminate\Events\Dispatcher	                events
                File	                Illuminate\Filesystem\Filesystem	            files
                Gate	                Illuminate\Contracts\Auth\Access\Gate
                Hash	                Illuminate\Contracts\Hashing\Hasher	            hash
                Lang	                Illuminate\Translation\Translator	            translator
                Log	                    Illuminate\Log\LogManager	log
                Mail	                Illuminate\Mail\Mailer	mailer
                Notification	        Illuminate\Notifications\ChannelManager
                Password	            Illuminate\Auth\Passwords\PasswordBrokerManager	auth.password
                Password (Instance)	    Illuminate\Auth\Passwords\PasswordBroker	    auth.password.broker
                Queue	                Illuminate\Queue\QueueManager	                queue
                Queue (Instance)	    Illuminate\Contracts\Queue\Queue	            queue.connection
                Queue (Base Class)	    Illuminate\Queue\Queue
                Redirect	            Illuminate\Routing\Redirector	                redirect
                Redis	                Illuminate\Redis\RedisManager	                redis
                Redis (Instance)	    Illuminate\Redis\Connections\Connection	        redis.connection
                Request	                Illuminate\Http\Request	                        request
                Response	            Illuminate\Contracts\Routing\ResponseFactory
                Response (Instance)	    Illuminate\Http\Response
                Route	                Illuminate\Routing\Router	                    router
                Schema	                Illuminate\Database\Schema\Builder
                Session	                Illuminate\Session\SessionManager	            session
                Session (Instance)	    Illuminate\Session\Store	                    session.store
                Storage	                Illuminate\Filesystem\FilesystemManager	        filesystem
                Storage (Instance)	    Illuminate\Contracts\Filesystem\Filesystem	    filesystem.disk
                URL	                    Illuminate\Routing\UrlGenerator	                url
                Validator	            Illuminate\Validation\Factory	                validator
                Validator (Instance)	Illuminate\Validation\Validator
                View	                Illuminate\View\Factory	                        view
                View (Instance)	        Illuminate\View\View

        </facadeClassReference>
    </facades>

    <contracts>
        <introduction>
            Laravel's Contracts are a set of interfaces that define the core  services
            provided by the framework. For example, a Illuminate\Contracts\Queue\Queue
            contract defines the methods needed for queueing jobs,           while the
            Illuminate\Contracts\Mail\Mailer contract defines the methods needed   for
            sending e-mail. Each contract has a corresponding implementation  provided
            by the framework. For example, Laravel provides a queue     implementation
            with  a variety of drivers. All of the Laravel contracts live in their own
            GitHub repository. This provides a quick reference point for all available
            https://github.com/illuminate/contracts .
            <contractVsFacades>
                Laravel's facades and helper functions provide a simple a way of utilizing
                Laravel's services without needing to type-hint and resolve contracts  out
                of the service container. In most cases, each facade has an     equivalent
                contract. Unlike facades, which do not require you to require them in your
                class's constructor, contracts allow you to define explicit   dependencies
                for your classes. Some developers prefer to explicitly define        their
                dependencies in this way and therefore prefer to use contracts,      while
                other developers enjoy the convenience of facades.
                <NOTE>
                    Most applications will be fine regardless of whether you prefer
                    facades   or contracts. However, if you are building a package,
                    you should strongly consider using contracts since they will be
                    easier to test in a package context.
                </NOTE>
            </contractVsFacades>
        </introduction>
        <whenToUseContracts>
            As discussed elsewhere, much of the decision to use contracts or   facades will
            come down to personal taste and the tastes of your development   team.     Both
            contract and facades can be used to create robust, well-tested          Laravel
            applications. As long as you are keeping your class's          responsibilities
            focused, you will notice very few practical differences between using contracts
            and facades. However, you may still have several questions regarding contracts.
            For example why use interfaces at all? Isn't using interfaces more complicated
            Let's distill the reasons for using interfaces.
            <looseCoupling>
                First, let's review some code thata is tightly coupled to a cache
                implementation.
                <!--

                    namespace App\Orders;

                    class Repository
                    {
                        /**
                         * The cache instance.
                         */
                        protected $cache;

                        /**
                         * Create a new repository instance.
                         *
                         * @param  \SomePackage\Cache\Memcached  $cache
                         * @return void
                         */
                        public function __construct(\SomePackage\Cache\Memcached $cache)
                        {
                            $this->cache = $cache;
                        }

                        /**
                         * Retrieve an Order by ID.
                         *
                         * @param  int  $id
                         * @return Order
                         */
                        public function find($id)
                        {
                            if ($this->cache->has($id)) {
                                //
                            }
                        }
                    }
                -->

                In this class, the code is tightly coupled to a given cache     implementation.
                It is tightly coupled because we are depending on a concrete     Cache    class
                from a package vendor. If the API of that package changes our     code     must
                change as well. Likewise, if we want to replace our underlying cache technology
                (Memcached) with another technology (Redis), we again will have to modify   our
                repository. Our repository should not have so much knowledge   regarding who is
                providing them data or how they are providing it.  Instead of this approach, we
                can improve our code by depending on a simple, vendor agnostic interface:

                <!--
                    namespace App\Orders;

                    use Illuminate\Contracts\Cache\Repository as Cache;

                    class Repository
                    {
                        /**
                         * The cache instance.
                         */
                        protected $cache;

                        /**
                         * Create a new repository instance.
                         *
                         * @param  Cache  $cache
                         * @return void
                         */
                        public function __construct(Cache $cache)
                        {
                            $this->cache = $cache;
                        }
                    }
                -->
                Now the code is not coupled to any specific vendor, or even Laravel.
                Since        the contracts package contains no implementation and no
                dependencies,  you may easily write an alternative implementation of
                any given contract allowing you to replace your cache implementation
                without modifying any of your cache consuming code.
            </looseCoupling>

            <simplicity>
                When all of Laravel's services are neatly defined  within simple
                interfaces, it is very easy to determine the       functionality
                offered by a given service. The contracts serve as      succinct
                documentation to the framework's features. In addition, when you
                depend on simple interfaces, your code is easier to   understand
                and maintain. Rather than tracking down which methods        are
                available to you within a large. complicated class, you      can
                refer to a simple, clean interface.
            </simplicity>

        </whenToUseContracts>

        <howToUseContracts>
            so how do you get an implementation of a contract? It's actually quite
            simple. Many types of classes in Laravel are resolved through      the
            service container, including controllers, event listeners,  middleware
            queued jobs, and even route Closures. So, to get an implementation  of
            a contract, you can just "type-hint" the interface in the  constructor
            of the class being resolved. for example, take a look at this    event
            listener:
            <!--
                namespace App\Listeners;

                use App\Events\OrderWasPlaced;
                use App\User;
                use Illuminate\Contracts\Redis\Factory;

                class CacheOrderInformation
                {
                    /**
                     * The Redis factory implementation.
                     */
                    protected $redis;

                    /**
                     * Create a new event handler instance.
                     *
                     * @param  Factory  $redis
                     * @return void
                     */
                    public function __construct(Factory $redis)
                    {
                        $this->redis = $redis;
                    }

                    /**
                     * Handle the event.
                     *
                     * @param  OrderWasPlaced  $event
                     * @return void
                     */
                    public function handle(OrderWasPlaced $event)
                    {
                        //
                    }
                }
            -->
            When the event listener is resolved, the service container will
            read the type-hinnts on the contructor of the class, and inject
            the appropriate value.
        </howToUseContracts>

        <contractReference>
            This table provides a quick reference to all of the Laravel contracts
            and their equivalent facades:
            https://laravel.com/docs/6.x/contracts#contract-reference

            Contract	                                                References Facade
            Illuminate\Contracts\Auth\Access\Authorizable
            Illuminate\Contracts\Auth\Access\Gate	                    Gate
            Illuminate\Contracts\Auth\Authenticatable
            Illuminate\Contracts\Auth\CanResetPassword
            Illuminate\Contracts\Auth\Factory	                        Auth
            Illuminate\Contracts\Auth\Guard	                            Auth::guard()
            Illuminate\Contracts\Auth\PasswordBroker	                Password::broker()
            Illuminate\Contracts\Auth\PasswordBrokerFactory	            Password
            Illuminate\Contracts\Auth\StatefulGuard
            Illuminate\Contracts\Auth\SupportsBasicAuth
            Illuminate\Contracts\Auth\UserProvider
            Illuminate\Contracts\Bus\Dispatcher	                        Bus
            Illuminate\Contracts\Bus\QueueingDispatcher	                Bus::dispatchToQueue()
            Illuminate\Contracts\Broadcasting\Factory	                Broadcast
            Illuminate\Contracts\Broadcasting\Broadcaster	            Broadcast::connection()
            Illuminate\Contracts\Broadcasting\ShouldBroadcast
            Illuminate\Contracts\Broadcasting\ShouldBroadcastNow
            Illuminate\Contracts\Cache\Factory	                        Cache
            Illuminate\Contracts\Cache\Lock
            Illuminate\Contracts\Cache\LockProvider
            Illuminate\Contracts\Cache\Repository	                    Cache::driver()
            Illuminate\Contracts\Cache\Store
            Illuminate\Contracts\Config\Repository	                    Config
            Illuminate\Contracts\Console\Application
            Illuminate\Contracts\Console\Kernel	                        Artisan
            Illuminate\Contracts\Container\Container	                App
            Illuminate\Contracts\Cookie\Factory	                        Cookie
            Illuminate\Contracts\Cookie\QueueingFactory	                Cookie::queue()
            Illuminate\Contracts\Database\ModelIdentifier
            Illuminate\Contracts\Debug\ExceptionHandler
            Illuminate\Contracts\Encryption\Encrypter	                Crypt
            Illuminate\Contracts\Events\Dispatcher	                    Event
            Illuminate\Contracts\Filesystem\Cloud	                    Storage::cloud()
            Illuminate\Contracts\Filesystem\Factory	                    Storage
            Illuminate\Contracts\Filesystem\Filesystem	                Storage::disk()
            Illuminate\Contracts\Foundation\Application	                App
            Illuminate\Contracts\Hashing\Hasher	                        Hash
            Illuminate\Contracts\Http\Kernel
            Illuminate\Contracts\Mail\MailQueue	                        Mail::queue()
            Illuminate\Contracts\Mail\Mailable
            Illuminate\Contracts\Mail\Mailer	                        Mail
            Illuminate\Contracts\Notifications\Dispatcher	            Notification
            Illuminate\Contracts\Notifications\Factory	                Notification
            Illuminate\Contracts\Pagination\LengthAwarePaginator
            Illuminate\Contracts\Pagination\Paginator
            Illuminate\Contracts\Pipeline\Hub
            Illuminate\Contracts\Pipeline\Pipeline
            Illuminate\Contracts\Queue\EntityResolver
            Illuminate\Contracts\Queue\Factory	                        Queue
            Illuminate\Contracts\Queue\Job
            Illuminate\Contracts\Queue\Monitor	                        Queue
            Illuminate\Contracts\Queue\Queue	                        Queue::connection()
            Illuminate\Contracts\Queue\QueueableCollection
            Illuminate\Contracts\Queue\QueueableEntity
            Illuminate\Contracts\Queue\ShouldQueue
            Illuminate\Contracts\Redis\Factory	                        Redis
            Illuminate\Contracts\Routing\BindingRegistrar	            Route
            Illuminate\Contracts\Routing\Registrar	                    Route
            Illuminate\Contracts\Routing\ResponseFactory	            Response
            Illuminate\Contracts\Routing\UrlGenerator	                URL
            Illuminate\Contracts\Routing\UrlRoutable
            Illuminate\Contracts\Session\Session	                    Session::driver()
            Illuminate\Contracts\Support\Arrayable
            Illuminate\Contracts\Support\Htmlable
            Illuminate\Contracts\Support\Jsonable
            Illuminate\Contracts\Support\MessageBag
            Illuminate\Contracts\Support\MessageProvider
            Illuminate\Contracts\Support\Renderable
            Illuminate\Contracts\Support\Responsable
            Illuminate\Contracts\Translation\Loader
            Illuminate\Contracts\Translation\Translator	                Lang
            Illuminate\Contracts\Validation\Factory	                    Validator
            Illuminate\Contracts\Validation\ImplicitRule
            Illuminate\Contracts\Validation\Rule
            Illuminate\Contracts\Validation\ValidatesWhenResolved
            Illuminate\Contracts\Validation\Validator	                Validator::make()
            Illuminate\Contracts\View\Engine
            Illuminate\Contracts\View\Factory	                        View
            Illuminate\Contracts\View\View	                            View::make()
        </contractReference>
    </contracts>
</architectureConcepts>

<theBasics>
    <routing>
        <basicRouting>
            The most basic Laravel routes accept a URI and a Closure, providing
            a very simple and expressive method of defining routes:
            <!--
                Route::get('foo', function () {
                    return 'Hello World';
                })
            -->

            The Default Route Files
            All Laravel routes are defined in your routes files, which are located
            in the routes directory. These files are automatically loaded by   the
            framework The routes/web.php file defines routes that are for your web
            interface. These routes are assigned the web middleware group,   which
            provides features like session state and CSRF protection. The   routes
            in routes/api.php are stateless and are assigned the    api middleware
            group For most applications, you will begin by defining routes in your
            routes/web.php file. The routes defined in routes/web.php       may be
            accessed by entering the defined route's URL in your browser.       For
            example you may access the following route by navigating             to
            http://your-app.test/user in your browser:

            <!--
                Route::get("/user', 'UserController@index');
            -->

            Routes defined in the routes/api.php file are nested within a  route
            group by the RouteServiceProvider. Within this group, the /api   URI
            prefix is automatically applied so you do not need to manually apply
            it to every route in the file. You may modify the prefix and   other
            route group options by modifying your RouteServiceProvider class.

            Available Router Methods
            The router allows you to register routes that respond to any HTTP verb:

            <!--
                Route::get($uri, $callback);
                Route::post($uri, $callback);
                Route::put($uri, $callback);
                Route::patch($uri, $callback);
                Route::delete($uri, $callback);
                Route::options($uri, $callback);
            -->

            Sometimes you may need to register a route that responds   to
            multiple HTTP verbs. You may do so using the match method  Or
            you may even register a route that responds to all HTTP verbs
            using the any method:

            <!--
                Route::match(['get', 'post'], '/', function () {
                    //
                });

                Route::any('/', function () {
                    //
                });
            -->

            CSRF Protection
            Any HTML form pointing to POST, PUT, or DELETE routes that   are
            defined in the web routes file should include a CSRF token field
            Otherwise, the request will be rejected.

            <redirectroutes>
                If you are defining a route that redirects to another URI, you
                may use the Route::redirect method. This method     provides a
                convenient shortcut so that you do not have to define a   full
                route or controller for performing a simple redirect:

                <!--
                    Route::redirect('/here', '/there');
                -->

                By default, Route::redirect returns a 302 status code. You may
                customize the status code using the optional third parameter:
                <!--
                    Route::redirect('/here', '/there', 301);
                -->

                You may use the Route::permanentRedirect method to return
                to return a 301 status code:
                <!--
                    Route::permanentRedirect('/here', '/there');
                -->
            </redirectroutes>
            <viewRoutes>
                if your route only needs to return a view, you may use the Route::view  method.
                Like the redirect method, this method provides a simple shortcut so that    you
                do not have to define a full route or controller. The view method accepts a URI
                as its first argument and a view name as its second argument. In addition,  you
                may provide an array of data to pass to the view as an optional third argument:
                <!--
                    Route::view('/welcome', 'welcome');
                    Route::view('/welcome', 'welcome', ['name' => 'Taylor');
                -->
            </viewRoutes>
        </basicRouting>
        <routeParameters>
            <requiredParameters>
                Sometimes you will need to capture segments of the URI within your route
                For example you may need to capture a user's ID from the URL. You may do
                so by defining route parameters:
                <!--
                    Route::get('user/{id}', function ($id) {
                        return 'UserId is = ' . $id;
                    });

                    Route::get('posts/{post}/comments/{comment}', function ($postId, $commentId) {
                        //
                    });
                -->
            </requiredParameters>

            <optionalParameters>
                Occasionally you may need to specify a route parameter, but make
                the presence of that route parameter optional. You may do so  by
                placing a ? mark after the parameter name. Make sure to give the
                route's corresponding variable a default value:

                <!--
                    Route::get('user/{$user?}', function ($userId = 5) {
                        //
                    });
                -->
            </optionalParameters>

            <regularExpressionConstraints>
                You may constrain the format of your route parameter using
                the where method on a route instance. the where     method
                accepts the name of the parameter and a regular expression
                defining how the parameter should be constrained:
                <!--
                    Route::get('user/{id}/{name}', function ($id, $name) {
                        //
                    })->where([
                        'id' => '[0-9]+',
                        'name' => '[a-z]+',
                        ]);
                -->

                Global Constraints

                If you like a route parameter to always be constrained by a given regular expression
                you may use the pattern method. You should define these patterns in the boot  method
                of your app/Providers/RouteServiceProvider.php :

                <!--
                    public function boot()
                    {
                        Route::pattern('id', '[0-9]+';

                        parent::boot();
                    }
                -->

                Once the pattern has been defined, it is automatically applied to all routes
                using that parameter name:

                <!--
                    Route::get('user/{id}', function ($id) {
                        //
                    });
                -->
                Encoded Forward Slashes
                The Laravel routing component allows all characters   except /.
                You must explicitly allow / to be part of you placeholder using
                a where condition regular expression:

                <!--
                    Route::get('search/{search}', function ($search) {
                        return $search;
                    })->where('search' , '.*');
                -->
            </regularExpressionConstraints>
        </routeParameters>
        <namedRoutes>
            Named routes allow the convenient generation of URLs or redirects for
            specific routes. You may specify a name for a route by chaining   the
            name method onto the route definition:

            <!--
                Route::get('user/{id}/profile', function($id) {
                    //
                })->name('profile');

                Route::get('user/profile', UserProfileController@show')->name('profile');
            -->

            Generating URLs to Named Routes
            Once you have assigned a name to a given route, you may use
            the route's name when generating URLs or redirects via  the
            global route function:

            <!--
                // Generating URLs..
                // https://website.com/user/id/profile
                $url = route('profile', chaining);

                // Generating Redirects
                return redirect()->route('profile', ['id' => 1);
            -->

            if you pass additional parameters in the array, those key / value pairs
            will automatically be added to the generated URL's query string:

            <!--
                Route::get('user/{id}/profile', function ($id) {
                    //
                })->name('profile');

                $url = route('profile', ['id' => 1, 'photos' => 'yes']);

                // /user/1/profile?photos=yes
            -->
            Inspecting The Current Route
            If you would like to determine if the current  request
            was routed to a given named route, you may use     the
            named method on a Route instance. For example, you may
            check the current route name from a route middleware:

            <!--
                /**
                 * Handle an incoming request.
                 *
                 * @param  \Illuminate\Http\Request  $request
                 * @param  \Closure  $next
                 * @return mixed
                 */
                public function handle($request, Closure $next)
                {
                    if ($request->route()->named('profile')) {
                        //
                    }

                    return $next($request);
                }
            -->
        </namedRoutes>
        <routeGroups>
            Route groups allow       you to share route attributes, such as middleware
            or namespaces,       across a large number of routes without needing    to
            define        those attributes on each individual route. Shared  attributes
            are specified in an array format as the first parameter to the Route::group
            method Nested groups attempt to intelligently "merge" attributes with their
            parent group. Middleware and where conditions are merged while       names,
            namespaces, and prefixes are appended. Namespace delimiters and slashes  in
            URI prefixes are automatically added where appropriate.

            <middleware>
                To assign middleware to all routes within a group, you may use the middleware
                method before defining the group. Middleware are executed in the order  they
                are listed in the array:

                <!--
                    Route::middleware(['first', 'second'])->group(function () {
                        Route::get('/', function () {
                            // Uses first and second middleware
                        });

                        Route::get('user/profile', function 9) {
                            // Uses first and Second middleware
                        });
                    });
                -->
            </middleware>

            <namespaces>
                Another common use-case for groups is assigning the same PHP namespace
                to a group of controllers using the namespace method:

                <!--
                    Route::namespace('Admin')->group(function () {
                        // Controllers Within The "App\Http\Controllers\Admin" Namespace
                    } );
                -->

                Remember, by default, the RouteServiceProvider includes your route files  within
                a namespace group, allowing you to register controller routes without specifying
                the full App\Http\Controllers namespace prefix. So, you only need to specify the
                namespace that comes after the base App\Http\Controllers namespace.
            </namespaces>

            <subdomainRouting>
                Route groups may also be used to handle subdomain routing.      Subdomains may be
                assigned route parameters just like route URIs, allowing you to capture a portion
                of the subdomain for usage in your route or controller. The subdomain may      be
                specified by calling the domain method before defining the group:

                <!--
                    Route::domain('{account}.myApp.com')->group(function() {
                        Route::get('user/{id}', function ($account, $id) {
                            //
                        });
                    });
                -->
                <NOTE>
                    In order to ensure your subdomain routes are reachable, you
                    should register subdomain routes before registering    root
                    domain routes. This will prevent root domain    routes from
                    overwriting subdomain routes which have the same URI path.
                </NOTE>
            </subdomainRouting>

            <routePrefixes>
                The prefix method may be used to prefix each route in the group
                with a given URI. For example, you may want to prefix all route
                URIs within the group with admin:

                <!--
                    Route::prefix('admin')->group(function () {
                        Route::get('users', function () {
                            // Matches The "/admin/users" URL
                        });
                    });
                -->

            </routePrefixes>

            <routeNamePrefixes>
                The name method may be used to prefix each route name in the group  with
                a given string. For example, you may want to prefix all of the   grouped
                route's names with admin. The given string is prefixed to the route name
                exactly as it is specified, so we will be sure to provide the trailing .
                character in the prefix:

                <!--
                    Route::name('admin.')->group(function () {
                        Route::get('users', function () {
                            // Route assigned name "admin.users"
                        }
                    })->name('users');
                -->
            </routeNamePrefixes>
        </routeGroups>
        <routeModelBinding>
            When injecting a model ID to a route or controller action, you will     often
            query to retrieve the model that corresponds to that ID. Laravel route  model
            binding provides a convenient way to automatically inject the model instances
            directly into your routes. For example, instead of injecting a user's ID, you
            can inject the entire User model instance that matches the given ID.
            <implicitBinding>
                Laravel automatically resolves Eloquent models defined in routes or  controller
                actions whose type-hinted variable names match a route segment name For example

                <!--
                    Route::get('api/users/{user}', function (App\User $user) {
                        return $user->email;
                    });
                -->

                Since the $user variable is type-hinted as the App\User Eloquent model and  the
                variable name matches the {user} URI segment, Laravel will automatically inject
                the model instance that has an ID matching the corresponding value from     the
                request URI. If a matching model instance is not found in the database, a   404
                HTTP response will automatically be generated.

                Customizing The Key Name
                If you would like model binding to use a database column other than    id
                when retrieving a given model class, you may override the getRouteKeyName
                method on the Eloquent model:

                <!--
                    /**
                     * Get the route key for the model.
                     *
                     * @return string
                     */
                    public function getRouteKeyName()
                    {
                        return 'slug';
                    }
                -->

            </implicitBinding>

            <explicitBinding>
                To register an explicit binding, use the router's model method       to specify
                the class for a given parameter. You should define your explicit model bindings
                in the boot method of the RouteServiceProvider class:

                <!--
                    public function boot()
                    {
                        parent::boot();

                        Route::model('user', App\User::class);

                        Route::get('profile/{user}', function (App\User $user) {
                            //
                        });
                    }
                -->

                Since we have bound all {user} parameters to the App\User model, a User
                instance will be injected into the route. So, for example, a request to
                profile/1 will inject the User instance from the database which has  an
                ID of 1.

                Customizing The Resolution Logic
                If you wish to use your own resolution logic, you may use the  Route::bind
                method. The Closure you pass to the bind method will receive the value  of
                the URI segment and should return the instance of the class that should be
                injected into the route:

                <!--
                    /**
                     * Bootstrap any application services.
                     *
                     * @return void
                     */
                     public function boot()
                     {
                        parent::boot();

                        Route::bind('user', function ($value) {
                            return App\User::where('name', $value)->firstOrFail();
                        });
                     }
                -->

                Alternatively, you may override the resolveRouteBinding method   on your
                Eloquent model. This method will receive the value of the URI    segment
                and should return the instance of the class that should be injected into
                the route:

                <!--
                    /**
                     * Retrieve the model for a bound value.
                     *
                     * @param  mixed  $value
                     * @return \Illuminate\Database\Eloquent\Model|null
                     */
                     public function resolveRouteBinding($value)
                     {
                        return $this->where('name', $value)->firstOrFail();
                     }

                -->

            </explicitBinding>

        </routeModelBinding>
        <fallbackRoutes>
            Using the Route::fallback method, you may define a routes     that
            will be executed when no other route matches the incoming  request
            Typically, unhandled requests will automatically render a 404 page
            via your applications's exception handler. However, since you  may
            define the fallback route within your routes/web.php file,     all
            middleware in the web middleware group will apply to the route You
            are free to add additional middleware to this route as needed:

            <!--
                Route::fallback(function () {
                    //
                });
            -->
            <NOTE>
                The fallback route should always be the last route
                registered by your application
            </NOTE>
        </fallbackRoutes>
        <rateLimiting>
            Laravel includes a middleware to rate limit access to routes within  your
            application. To get started, assign the throttle middleware to a route or
            a group of routes. The throttle middleware accepts two parameters    that
            determine the maximum number of requests that can be made in a      given
            number of minutes. For example, let's specify that an authenticated  user
            may access the following group of routes 60 times per minute:

            <!--
                Route::middleware('auth:api', 'throttle:60,1')->group(function () {
                    Route::get('/user', function () {
                        //
                    });
                });
            -->

            Dynamic Rate Limiting
            You may specify a dynamic request maximum based on an attribute of the
            authenticated User mode. For example, if your User model    contains a
            rate_limit attribute, you may pass the name of the attribute to    the
            throttle middleware so that it is used to calculate the maximum request
            count:
            <!--
                Route::middleware('auth:api', 'throttle:rate_limit,1')->group(function () {
                    Route::get('/user', function () {
                        //
                    });
                });
            -->

            Distinct Guest & Authenticated User Rate Limits
            Your may specify different rate limits for guest and authenticated   users.
            For example, you may specify a maximum of 10 requests per minute for guests
            6- for authenticated users:

            <!--
                Route::middleware('throttle:10|60,1')->group(function () {
                    //
                });
            -->
            You may also combine this functionality with dynamic rate  limit.
            For example, if your User model contains a rate_limit  attribute,
            you may pass the name of the attribute to the throttle middleware
            so that it is used to calculate the maximum request count     for
            authenticated users:

            <!--
                Route::middleware('auth:api', 'throttle:10|rate_limit,1')->group(function () {
                    Route::get('/user', function () {
                        //
                    });
                });
            -->

            Rate Limit Segments
            Typically, you will probably specify one rate limit for your entire   API.
            However, your application may require different rate limits for  different
            segments of your API. If this is the case, you will need to pass a segment
            name as the third argument to the throttle middleware:

            <!--
                Route::middleware('auth:api')->group(function () {
                    Route::middleware('throttle:60,1,default')->group(function () {
                        Route::get('/servers', function () {
                            //
                        });
                    });

                    Route::middleware('throttle:60,1,deletes')->group(function () {
                        Route::delete('/servers/{id}', function () {
                            //
                        });
                    });
                });
            -->
        </rateLimiting>
        <formMethodSpoofing>
            HTML forms do not support PUT, PATCH or DELETE actions. So, when defining
            PUT, PATCH or DELETE routes that are called from an HTML form, you   will
            need to add a hidden _mehtod field to the form. The value sent with   the
            _method field will be used as theHTTP request method:

            <!--
                <form action="/foo/bar" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>

                OR

                    <form action="/foo/bar" method="POST">
                        @method('PUT')
                        @csrf
                    </form>
            -->
        </formMethodSpoofing>
        <accessingTheCurrentRoute>
            Accessing The Current Route
            You may use the current, currentRouteName, and currentRouteAction methods
            on the Route facade to access information about the route handling    the
            incoming request:

            <!--
                $route = Route::current();
                $name = Route::currentRouteName();
                $action = Route::currentRouteAction();
            -->

        </accessingTheCurrentRoute>
    </routing>
    <middleware>
        <introduction>
            Middleware provide a convenient mechanism for filtering HTTP requests entering your
            application. For example, Laravel includes a middleware that verifies the user   of
            your application is authenticated. If the user is not authenticated, the middleware
            will redirect the user to the login screen However if the user is authenticated the
            the middleware will allow the request to proceed further into the      application.
            Additional middleware can be written to perform a variety of tasks          besides
            authentication A CORS middleware might be responsible for adding the proper headers
            to all responses leaving your application. A logging middleware might log       all
            incoming requests to your application. There are several middleware included in the
            Laravel framework, including middleware for authentication and CSRF protection. All
            of these middleware are located in the app/Http/Middleware directory.
        </introduction>
        <definingMiddleware>
            To create a new middleware, use the make:middleware Artisan command:
            <!--
                php artisan make:middleware CheckAge
            -->
            This command will place a new CheckAge class within your app/Http/Middleware directory.
            In this middleware, we will only allow access to the route if the supplied age       is
            greater than 200. Otherwise, we will redirect the users back to the home URI:
            <!--
                namespace App\Http\Midleware

                use Closure;

                class CheckAge
                {
                    /**
                     * Handle an incoming request.
                     *
                     * @param  \Illuminate\Http\Request  $request
                     * @param  \Closure  $next
                     * @return mixed
                     */
                     public function handle($request, Closure $next)
                     {
                        if ($request->age <= 200) {
                            return redirect('home');
                        }

                        return $next($request);
                     }
                }
            -->
            As you can see, if the given age is less than or equal to 200, the middleware
            will return an HTTP redirect to the client; otherwise, the request will    be
            passed further into the application. To pass the request deeper into      the
            application (allowing the middleware to pass), call the $next callback   with
            the $request. It's best to envision middleware as a series of "layers"   HTTP
            requests must pass through before they hit your application. Each layer   can
            examine the request and even reject it entirely.

            <NOTE>
                All middleware are resolved via the service container, so you may
                type-hint any dependencies you need within a         middleware's
                constructor.
            </NOTE>

            Before & After Middleware
            Whether a middleware run before or after a request depends on
            the middleware itself. For example, the following  middleware
            would perform some task before the request is handled by  the
            application:

            <!--
                namespace App\Http\Middleware;

                use Closure;

                class BeforeMiddleware
                {
                    public function handle($request, Closure $next)
                    {
                        // Perform action

                        return $next($request);
                    }
                }
            -->

            However, this middleware would perform its task
            after the request is handled by the application:

            <!--
                namespace App\Http\Middleware;

                use Closure;

                class AfterMiddleware
                {
                    public function handle($request, Closure $next)
                    {
                        $response = $next($request);

                        // Perform action

                        return $response;
                    }
                }
            -->
        </definingMiddleware>
        <registeringMiddleware>
            <globalMiddleware>
                If you want a middleware to run during every HTTP   request to
                your application, list the middleware class in the $middleware
                property of your app\Http\Kernel.php class.
            </globalMiddleware>

            <assigningMiddlewareToRoutes>
                If you would like to assign middleware to specific routes, you should  first
                assign the middleware a key in your app/Http/Kernel.php. By default,     the
                $routeMiddleware property of this class contains entries for the  middleware
                included with Laravel. To add your own, append it to this list and assign it
                a key of your choosing. Once the middleware has been defined in the     HTTP
                kernel, you may use the middleware method to assign middleware to a route:

                <!--
                    Route::get('admin/profile', function () {
                        //
                    })->middleware('auth');
                -->

                You may also assign multiple middleware to the route:

                <!--
                    Route::get('admin/profile', function () {
                        //
                    })->middleware('auth', 'second');
                -->

                When assigning middleware, you may also pass the fully qualified class name:

                <!--
                    use App\Http\Middleware\CheckAge;

                    Route::get('admin/profile', function () {
                        //
                    })->middleware(CheckAge::class);
                -->

            </assigningMiddlewareToRoutes>

            <middlewareGroups>
                Sometimes you may want to group several middleware under a    single
                key to make them easier to assign to routes. You may do this   using
                the $middlewareGroups property of your HTTP kernel. Out of the   box
                Laravel comes with web and api middleware groups that contain common
                middleware you may want to apply to your web UI and API      routes.
                Middleware groups may be assigned to routes and controller   actions
                using the same syntax as individual middleware. Again,    middleware
                groups make it more convenient to assign many middleware to a  route
                at once:

                <!--
                    Route::get('/', function () {
                        //
                    })->middleware('web');

                    Route::group(['middleware' => ['web']], function () {
                        //
                    });

                    Route::middleware(['web', 'subscribed'])->group(function () {
                        //
                    });
                -->

                <NOTE>
                    Out of the box, the web middleware group is        automatically
                    applied to your routes/web.php file by the RouteServiceProvider.
                </NOTE>
            </middlewareGroups>

            <sortingMiddleware>
                Rarely, you may need your middleware to execute in a specific order but
                not have control over their order when they are assigned to the  route.
                In this case, you may specify your middleware priority using        the
                $middlewarePriority property of your app/Http/Kernel.php file.
            </sortingMiddleware>

        </registeringMiddleware>
        <middlewareParameters>
            Middleware can also receive additional parameters. For example,  if   your
            application needs to verify that the authenticated user has a  given  role
            before performing a given action, you could create a CheckRole  middleware
            that receives a role name as an additional argument. Additional middleware
            parameters will be passed to the middleware after the $next argument:

            <!--

                namespace App\Http\Middleware;

                use Closure;

                class CheckRole
                {
                    /**
                     * Handle the incoming request.
                     *
                     * @param  \Illuminate\Http\Request  $request
                     * @param  \Closure  $next
                     * @param  string  $role
                     * @return mixed
                     */
                    public function handle($request, Closure $next, $role)
                    {
                        if (! $request->user()->hasRole($role)) {
                            // Redirect...
                        }

                        return $next($request);
                    }

                }
            -->
            Middleware parameters may be specified when defining the route by
            separating the middleware name and parameters with :     Multiple
            parameters should be delimited by commas:

            <!--
                Route::get()->middleware('role:editor');
            -->
        </middlewareParameters>
        <terminableMiddleware>
            Sometimes a middleware may need to do some work after the   HTTP
            response has been sent to the browser. If you define a terminate
            method on your middleware and your web server is using  FastCGI,
            the terminate method will automatically be called after      the
            response is sent to the browser:

            <!--
                namespace Illuminate\Session\Middleware;

                use Closure;

                class StartSession
                {
                    public function handle($request, Closure $next)
                    {
                        return $next($request);
                    }

                    public function terminate($request, $response)
                    {
                        // Store the session data...
                    }
                }
            -->

            The terminate method should receive both the request and the response. Once
            you have defined a terminable middleware, you should add it to the list  of
            route or global middleware in the app/Http/Kernel.php file.

            When calling the terminate method on your middleware, Laravel will   resolve a
            fresh instance of the middleware from the service container. If you would like
            to use the same middleware instance when the handle and terminate methods  are
            called, register the middleware with the container using the       container's
            singleton method. Typically this should be done in the register method of your
            AppServiceProvider.php:

            <!--
                use App\Http\Middleware\TerminableMiddleware;

                /**
                 * Register any application services.
                 *
                 * @return void
                 */
                public function register()
                {
                    $this->app->singleton(TerminableMiddleware::class);
                }
            -->
        </terminableMiddleware>
    </middleware>
    <CSRFProtection>
        <introduction>
            Laravel makes ite easy to protect your application from cross-site request forgery (CSRF)
            attacks. CSRFs are a type of malicious exploit whereby unauthorized commands          are
            performed on behalf of an authenticated user Laravel automatically generates a CSRF token
            for each active user session managed by the application. This token is used to     verify
            that the authenticated user is the one actually making the requests to the   application.
            Anytime you define an HTML form in your application, you should include a hidden     CSRF
            token field in the form so that the CSRF protection middleware can validate the  request.
            You may use the @csrf Blade directive to generate the token field.

            The VerifyCsrfToken middleware, which is included in the web middleware group,  will
            automatically verify that the token in the request input matches the token stored in
            the session.

            CSRF Tokens & JavaScript
            When building JavaScript driven applications, it is convenient to have your JavaScript
            HTTP library automatically attach the CSRF token to every outgoing request. By default
            the Axios HTTP library provided in the resources/js/bootstrap.js file    automatically
            sends an X-XSRF-TOKEN header using the value of the encrypted XSRF-TOKEN cookie If you
            are not using this library, you will need to manually configure this behavior for your
            application.
        </introduction>
        <excludingURIsFromCSRFProtection>
            Sometimes you may wish to exclude a set of URIs from CSRF protection. For example, if you are using Stripe
            to process payments and are utilizing their web-hook system, you will need to exclude your Stripe web-hook
            handler route from CSRF protection since Stripe will not know what CSRF token to send to your routes.

            Typically, you should place these kinds of routes outside of the web middleware group  that
            the RouteServiceProvider applies to all routes in the routes/web.php file. However you  may
            also exclude the routes by adding their URIs to the $except property of the VerifyCsrfToken
            middleware:

            <!--

                namespace App\Http\Middleware;

                use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

                class VerifyCsrfToken extends Middleware
                {
                    /**
                     * The URIs that should be excluded from CSRF verification.
                     *
                     * @var array
                     */
                    protected $except = [
                        'stripe/*',
                        'http://example.com/foo/bar',
                        'http://example.com/foo/*',
                    ];
                }
            -->

            <NOTE>
                The CSRF middleware is automatically disabled when running tests
            </NOTE>
        </excludingURIsFromCSRFProtection>
        <X_CSRF_Token>
            In addition to checking for the CSRF token as a POST parameter,     the
            VerifyCsrfToken middleware will also check for the X-CSRF-TOKEN request
            header. You could, for example, store the token in an HTML meta tag:

            <!--
                <meta name="csrf-token" content="{{ csrf_token() }}">
            -->

            Then, once you have created the meta tag, you can instruct a  library
            like jQuery to automatically add the token to all request    headers.
            This provides simple, conveninent CSRF protection for your AJAX based
            applications:

            <!--
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            -->
        </X_CSRF_Token>

        <X_XSRF_Token>
            Laravel stores the current CSRF token in an encrypted XSRF-TOKEN cookie that is included
            with each response generated by the framework. You can use the cookie value to set   the
            X-XSRF-TOKEN request header. This cookie is primarily sent as convenience since some  JS
            frameworks and libraries, like Angular and Axios, automatically place its value in   the
            X-XSRF-TOKEN header on same-origin requests.
        </X_XSRF_Token>
        <NOTE>
            By default, the resources/js/bootstrap.js file includes the Axios HTTp library
            which will automatically send this for you.
        </NOTE>
    </CSRFProtection>
    <controllers>
        <introduction>
            instead of defining all of your request handling logic as Closures in
            route files, you may wish ot organize this behavior using  Controller
            classes. Controllers can group related request handling logic into  a
            single class. Controllers are stored in the app/Http/Controllers dir.
        </introduction>
        <basicControllers>
            <definingControllers>
                Below is an example of a basic Controller class. Note that the Controller
                extends the base Controller class included with Laravel. The base   class
                provides a few convenience methods such as the middleware method,   which
                may be used to attach middleware to controller actions:

                <!--
                    namespace App\Http\Controllers;

                    use App\Http\Controllers\Controller;
                    use App\User;

                    class UserController extends Controller
                    {
                        /**
                         * Show the profile for the given user.
                         *
                         * @param  int  $id
                         * @return View
                         */
                        public function show($id)
                        {
                            return view('user.profile', ['user' => User::findOrFail($id)]);
                        }
                    }
                -->

                You can define a route to this controller action like so:

                <!--
                    Route::get('user/{id}', 'UserController@show');
                -->

                Now, when a request matches the specified route URI, the show method
                on the UserController class will be executed.

                <NOTE>
                    Controllers are not required to extend a base       class
                    However, you will not have access to convenience features
                    such as the middleware, validate, and dispatch methods.
                </NOTE>
            </definingControllers>
            <ControllersAndNamespaces>
                It is very important to note that we did not need to specify the full     controller
                namespace when defining the controller route. Since the RouteServiceProvider   loads
                your route files within a route group that contains the namespace, we only specified
                the portion of the class name that comes after the App\Http\Controllers portion   of
                the namespace.

                If you choose to nest your controllers deeper into the App\Http\Controllers directory
                use the specific class name relative to the App\Http\Controllers root namespace.  So,
                if your full controller class is App\Http\Controllers\Photos\AdminController,     you
                should register routes to the controller like so:

                <!--
                    Route::get('foo', 'Photos\AdminController@method');
                -->
            </ControllersAndNamespaces>
            <singleActionControllers>
                If you would like to define a controller that only handles a single action
                you may place a single __invoke method on the controller:

                <!--
                    namespace App\Http\Controllers;

                    use App\Http\Controllers\Controller;
                    use App\User;

                    class ShowProfile extends Controller
                    {
                        /**
                         * Show the profile for the given user.
                         *
                         * @param  int  $id
                         * @return View
                         */
                        public function __invoke($id)
                        {
                            return view('user.profile', ['user' => User::findOrFail($id)]);
                        }
                    }
                -->

                When registering routes for single action controllers, you do not
                need to specify a method:

                <!--
                    Route::get('user/{id}', 'ShowProfile');
                -->

                You may generate an invokable controller by using the --invokable option
                of the make:controller Artisan command:

                <!--
                    php artisan make:controller ShowProfile --invokable
                -->
            </singleActionControllers>
        </basicControllers>

        <ControllerMiddleware>
            Middleware may be assigned to the controller's routes in your
            route files:

            <!--
                Route::get("profile', 'UserController@shwo')->middleware('auth');
            -->

            However, it is more convenient to specify middleware within your controller's
            constructor. Using the middleware method from your controller's  constructor,
            you may easily assign middleware to the controller's action. You may     even
            restrict the middleware rto only certain methods on the controller class:

            <!--
                class UserController extends Controller
                {
                    /**
                     * Instantiate a new controller instance.
                     *
                     * @return void
                     */
                    public function __construct()
                    {
                        $this->middleware('auth');

                        $this->middleware('log')->only('index');

                        $this->middleware('subscribed')->except('store');
                    }
                }
            -->

            controllers also allow you to register middleware using a Closure.
            This provides a convenient way to define a middleware for a single
            controller without defining an entire middleware class:

            <!--
                $this->middleware(function () {
                    return $next($request);
                });
            -->

            <NOTE>
                You may assign middleware to a subset of controller actions
                however, it may indicate your controller is growing to large
                instead, consider breaking your controller into     multiple
                smaller controllers
            </NOTE>
        </ControllerMiddleware>
        <resourceControllers>
            Laravel resource routing assigns the typical "CURD" routes to a    controller
            with a single line of code. For example, you may wish to create a  controller
            that handles all HTTP requests for "photos" stored by your application. Using
            the make:controller Artisan command, we can quickly create such a controller:
            <!--
                php artisan make:controller PhotoController --resource
            -->

            This command will generate a controller at app/Http/Controllers/PhotoController.php
            The controller will contain a method for each of the available resource operations
            Next, you may register a resourceful route to the controller:

            <!--
                Route::resource('photos', 'PhotoController');
            -->

            This single route declaration creates multiple routes to handle a variety of   actions
            on the resource. The generated controller will already have methods stubbed  for  each
            of these actions, including notes informing you of the HTTP verbs and URIs they  handle
            You may register many resource controllers at once by passing an array to the resources
            method:

            <!--
                Route::resources([
                    'photos' => 'PhotoController',
                    'posts' => 'PostController',
                ]);
            -->

            Actions Handled By Resource Controller

            Verb	    URI	                    Action	    Route Name

            GET	        /photos	                index	    photos.index
            GET	        /photos/create	        create	    photos.create
            POST	    /photos	                store	    photos.store
            GET	        /photos/{photo}	        show	    photos.show
            GET	        /photos/{photo}/edit	edit	    photos.edit
            PUT/PATCH	/photos/{photo}	        update	    photos.update
            DELETE	    /photos/{photo}	        destroy	    photos.destroy

            Specifying The Resource Model

            If you are using route model binding and would like the resource controller's methods
            to type-hint a model instance, you may use the --model option when generating     the
            controller:

            <!--
                php artisan make:controller PhotoController --resource --model=Photo
            -->

            Spoofing Form Methods
            Since HTMl forms can't make PUT, PATCH, or DELETE requests, you will  need
            to add a hidden _method field to spoof these HTTP verbs. The @method Blade
            directive can create this field for you:

            <!--
                <form action="/foo/bar" method="POST">
                    @method('PUT')
                </form>
            -->
            <partialResourceRoutes>
                When declaring a resource route, you may specify a subset of     actions
                the controller should handle instead of the full set of default actions:

                <!--
                    Route::resource('photos', 'PhotoController')->only([
                        'index', 'show'
                    ]);

                    Route::resource('photos', 'PhotoController')->except([
                        'index', 'show'
                    ]);
                -->

                API Resource Routes
                When declaring resource routes that will be consumed by APIs,         you will
                commonly want to exclude routes that present HTML templates such     as create
                and edit. For convenience, you may use the apiResource method to automatically
                exclude these two routes:

                <!--
                    Route::apiResource('photos', 'PhotoController');
                -->

                You may register many API resource controllers at once by passing an array
                to the apiResources method. To quickly generate an API resource controller
                that does not include the create or edit methods, the --api switch    when
                executing the make:controller command:
                <!--
                    php artisan make:controller API/PhotoController --api
                -->
            </partialResourceRoutes>
            <nestedResources>
                Sometimes you may need to define routes to a nested resource. For     example,
                a photo resource may have multiple comments that may be attached to the photo.
                To nest the resource controllers, use "dot" notation in your route declaration

                <!--
                    Route::resource('photos.comments', 'PhotoCommentController');
                -->
                This route will register a nested resource that may be accessed with
                URIs like the following: /photos/{photo}/comments/{comment}

                Shallow Nesting
                Often, it is not entirely necessary to have both the parent and the  child
                IDs within a URI since the child ID is already a unique identifier.   When
                using unique identifier such as auto-incrementing primary keys to identify
                your models in URI segments, you may choose to use "shallow nesting":

                <!--
                    Route::resource('photos.comments', 'CommentController')->shallow();
                -->

                The route definition above will define the following routes:

                Verb	   |    URI	                             |  Action	 |   Route Name
                ________________________________________________________________________________________
                GET        |    /photos/{photo}/comments	     |  index	 |   photos.comments.index  |
                GET        |    /photos/{photo}/comments/create	 |  create	 |   photos.comments.create |
                POST       |    /photos/{photo}/comments	     |  store	 |   photos.comments.store  |
                GET        |	/comments/{comment}	             |  show     |   comments.show          |
                GET        |	/comments/{comment}/edit	     |  edit	 |   comments.edit          |
                PUT/PATCH  |    /comments/{comment}	             |  update   |   comments.update        |
                DELETE	   |    /comments/{comment}	             |  destroy	 |   comments.destroy       |

            </nestedResources>
            <namingResourceRoutes>
                By default, all resource controller actions have a route name; however, you
                can override these names by passing a names array with your options:

                <!--
                    Route::resource('photos', 'PhotoController')->names([
                        'create' => 'photos.build',
                    ]);
                -->
            </namingResourceRoutes>
            <namingResourceRoutesParameters>
                By default, Route::resource will create the route parameters for  your
                resource routes based on the singularized version of the resource name
                You can easily override this on a per resource basis by using      the
                parameters method should be an associative array of resource names and
                parameter names:

                <!--
                    Route::resource('users', 'AdminUserController')->parameters([
                        'users' => 'admin_user'
                    ]);
                -->
                The example above generates the following URIs
                for the resource's show route:
                <!--
                    /users/{admin_user}
                -->
            </namingResourceRoutesParameters>
            <localizingResourceURIs>
                By default, Route::resource will create resource URIs using English verbs
                If you need to localize the create and edit action verbs, you may use the
                Route::resourceVerbs method. This may be done in the boot method of  your
                AppServiceProvider:

                <!--
                    use Illuminate\Support\Facades\Route;

                    /**
                     * Bootstrap any application services.
                     *
                     * @return void
                     */
                    public function boot()
                    {
                        Route::resourceVerbs([
                            'create' => 'crear',
                            'edit' => 'editar',
                        ]);
                    }
                -->

                Once the verbs have been customized, a resource route registration
                such as Route::resource('fotos', 'PhotoController') will   produce
                the following URIs:
                <!--
                    /fotos/crear
                    /fotos/{foto}/editar
                -->
            </localizingResourceURIs>
            <supplementingResourceControllers>
                If you need to add additional routes to a resource controller  beyond
                the default set of resource routes, you should define those    routes
                before your call to Route::resource; otherwise, the routes defined by
                the resource method may unintentionally take precedence over     your
                supplemental routes:
                <!--
                    Route::get('photos/popular', 'PhotoController@method');
                    Route::resource('photos', 'PhotoController');
                -->

                <NOTE>
                    Remember to keep your controllers focused. If you find  yourself
                    routinely needing methods outside of the typical set of resource
                    actions, consider splitting your controller into two,    smaller
                    controllers.
                </NOTE>
            </supplementingResourceControllers>
        </resourceControllers>
        <dependencyInjectionAndControllers>
            Constructor Injection
            The Laravel service container is used to resolve all Laravel controllers.
            As a result, you are able to type-hint any dependencies your   controller
            may need in its constructor. The declared dependencies will automatically
            be resolved and injected into the controller instance:
            <!--
                namespace App\Http\Controllers;

                use App\Repositories\UserRepository;

                class UserController extends Controller
                {
                    /**
                     * The user repository instance.
                     */
                    protected $users;

                    /**
                     * Create a new controller instance.
                     *
                     * @param  UserRepository  $users
                     * @return void
                     */
                    public function __construct(UserRepository $users)
                    {
                        $this->users = $users;
                    }
                }
            -->
            You may also type-hint any Laravel contract. If the container can resolve
            it, you can type-hint it. Depending on your application, injecting   your
            dependencies into your controller may provide better testability.

            Method injection
            In addition to constructor injection, you may also type-hint   dependencies
            on your controller's methods. A common use-case for method injection     is
            injecting the Illuminate\Http\Request instance into your controller methods

            <!--

                namespace App\Http\Controllers;

                use Illuminate\Http\Request;

                class UserController extends Controller
                {
                    /**
                     * Store a new user.
                     *
                     * @param  Request  $request
                     * @return Response
                     */
                    public function store(Request $request)
                    {
                        $name = $request->name;

                        //
                    }
                }
            -->
            If your controller method is also expecting input from a    route
            parameter, list your route arguments after your other dependencies
            For example, if your route is defined like so:
            <!--
                Route::put('user/{id}', 'UserController@update');
            -->
            You may still type-hint the Illuminate\Http\Request and access your
            id parameter by dfining your controller method as follows:

            <!--
                namespace App\Http\Controllers;

                use Illuminate\Http\Request;

                class UserController extends Controller
                {
                    /**
                     * Update the given user.
                     *
                     * @param  Request  $request
                     * @param  string  $id
                     * @return Response
                     */
                    public function update(Request $request, $id)
                    {
                        //
                    }
                }
            -->
        </dependencyInjectionAndControllers>
        <routeCaching>
            <NOTE>
                Closure based routes cannot be cached. To use route caching, you
                must convert any Closure routes to controller classes.
            </NOTE>
            If your application is exclusively using controller based routes, you
            should take advantage of Laravel's route cache. Using the route cache
            will drastically decrease the amount of time it takes to register all
            of your application's routes. In some cases, your route  registration
            may even be up to 100x faster. To generate a route cache just execute
            the route:cache Artisan command:
            <!--
                php artisan route:cache
            -->
            After running this command, your cached routes file will be loaded on
            every request. Remember, if you add any new routes you will need   to
            generate a fresh route cache. Because of this, you should only    run
            the route:cache command during your project's deployment. You may use
            the route:clear command to clear the route cache.
        </routeCaching>
    </controllers>
    <requests>
        To obtain an instance of the current HTTP request via dependency injection, you
        should type-hint the Illuminate\Http\Request class on your controller   method.
        The incoming request instance will automatically be injected by the     service
        container:

        <!--
            namespace App\Http\Controllers;

            use Illuminate\Http\Request;

            class UserController extends Controller
            {
                /**
                 * Store a new user.
                 *
                 * @param  Request  $request
                 * @return Response
                 */
                public function store(Request $request)
                {
                    $name = $request->input('name');

                    //
                }
            }
        -->

        Dependency Injection & Route Parameters
        If your controller method is also expecting input from a route   parameter
        you should list your route parameters after your othe dependency. example:

        <!--
            Route::put('user/{id}', 'UserController@update');


            namespace App\Http\Controllers;

            use Illuminate\Http\Request;

            class UserController extends Controller
            {
                /**
                 * Update the specified user.
                 *
                 * @param  Request  $request
                 * @param  string  $id
                 * @return Response
                 */
                public function update(Request $request, $id)
                {
                    //
                }
            }
        -->
        Accessing The Request Via Route Closures
        You may also type-hint the Illuminate\Http\Request class on a route Closure.  The
        service container will automatically inject the incoming request into the Closure
        when it is executed:

        <!--
            use Illuminate\Http\Request;

            Route::get('/', function (Request $request) {
                //
            });
        -->
        <accessingTheRequest>
            <requestPathAndMethod>
                The Illuminate\Http\Request instance provides variety of methods
                for examining the HTTP request for your application and  extends
                the Symofny\Component\httpFoundation\Request class. We      will
                discuss a few of the most important methods below.

                Retrieving The Request Path
                The path method returns the request's path information. So, if
                the incoming request is targeted at http://domain.com/foo/bar,
                tha path method will return foo/bar:

                <!--
                    $uri = $request->path();
                -->

                The is method allows you to verify that the incoming request  path
                matches a given pattern. You may use the * character as a wildcard
                when utilizing this method:

                <!--
                    $request->is('admin/*'); // true or false
                -->

                Retrieving The Request URL
                To retrieve the full URL for the incoming request you may use the    url
                or fullUrl methods. The url method will return the URL without the query
                string, while the fullUrl method includes teh query string:

                <!--
                    // Without Query String...
                    $url = $request->url();

                    // With Query String...
                    $url = $request->fullUrl();
                -->

                Retrieving The Request Method
                The method method will return the HTTP verb for the request. You may
                use the isMethod method to verify that the HTTP verb matches a given
                string:

                <!--
                    $method = $request->method();

                    if ($request->isMethod('post')) {
                        //
                    }
                -->
            </requestPathAndMethod>
            <PSR-7Requsts>
                The <a href="https://www.php-fig.org/psr/psr-7/">PSR-7 standard </a>
                specifies interfaces for HTTP messages, including requests       and
                responses If you would like to obtain an instance of a PSR-7 request
                instead of a Laravel request, you will first need to install a   few
                libraries. Laravel uses the Symfony HTTP Message Bridge component to
                convert typical Laravel requsts and responses into PSR-7  compatible
                implementations:

                <!--
                    composer require symfony/psr-http-message-bridge
                    composer require nyholm/psr7
                -->

                Once you have installed these libraries, you may obtain PSR-7 request
                by type-hinting the request interface on your route Closure        or
                controller method:

                <!--
                    use Psr\Http\Message\ServerRequestInterface;

                    Route::get('/', function (ServerRequestInterface $request) {
                        //
                    });
                -->

                <NOTE>
                    If you return a PSR-7 response instance from a  route
                    or controller it will automatically be converted back
                    to Laravel response instance and be displayed by  the
                    framework.
                </NOTE>
            </PSR-7Requsts>
        </accessingTheRequest>
        <inputTrimmingAndNormalization>
            Be default, Laravel includes the TrimStrings and ConvertEmptyStringsToNull
            middleware in your application's global middleware stack. These middleware
            are listed in the stack by the App\Http\Kernel class These middleware will
            automatically trim all incoming string fields on the request, as well   as
            convert any empty string fields to null. This allows you to not have    to
            worry about these normalization concerns in your routes and controllers If
            you would like to disable this behavior, you may remove the two middleware
            from your application's middleware stack by removing them from         the
            $middleware property of your App\Http\Kernel class.
        </inputTrimmingAndNormalization>
        <retrievingInput>
            Retrieving All Input Data
            You may also retrieve all of the input data as an array using the all method:

            <!--
                $input = $request->all();
            -->

            Retrieving An Input Value
            Using a few simple methods, you may access  all of the user input from your
            Illuminate\Http\Request instance without worrying about which HTTP verb was
            used for the request. Regardless of the HTTP verb, the input method may  be
            used to retrieve user input:

            <!--
                $name = $request->input('name');
            -->

            You may pass a default value as the second argument to the input method.
            This value will be returned if the requested input value is not  present
            on teh request:
            <!--
                $name = $request->input('name', 'Sally');
            -->

            When working with forms that contain array inputs, use "dot" notation
            to access the arrays:

            <!--
                $name = $request->input('products.0.name');
                $name = $request->input('products.*.name');
            -->

            You may call the input method without any arguments in order
            to retrieve all of the input values as an associative array:

            <!--
                $input = $request->input();
            -->

            Retrieving Input From The Query String
            While the input method retrieves values from entire request
            payload (including the query string), the query method will
            only retrieve values from the query string:

            <!--
                $name = $request->query('name');
            -->

            If the requested query string value data is not present, the
            second argument to this method will be returned:

            <!--
                $name = $request->query('name', 'Helen');
            -->

            You may call the query method without any arguments in order
            to retrieve all of the query string values as an associative
            array:

            <!--
                $query = $request->query();
            -->

            Retrieving Input Via Dynamic Properties
            You may also access user input using dynamic properties on the
            Illuminate\Http\Request instance. For example, if one of  your
            application's forms contains a name field, you may access  the
            value of the field like so:

            <!--
                $name = $request->name;
            -->

            When using dynamic properties, Laravel will first look for the
            parameter's value in the request payload. If it is no present,
            Laravel will search for the field in the route parameters.

            Retrieving JSON Input Values
            When sending JSON requests to your application, you may  access
            the  JSON data via the input method as long as the Content-Type
            header of the request is properly set to application/json.  You
            may even use "dot" syntax to dig into JSON arrays:

            <!--
                $name = $request->input('user.name');
            -->

            Retrieving Boolean Input Values
            When dealing with HTML elements like check boxes, your application may
            receive "truthy" values that are actually strings. For example, "true"
            or "on". For convenience, you may use the boolean method to   retrieve
            these values as booleans. The boolean method returns true for 1,  "1",
            true, "true", "on", and "yes". All other values will return false:

            <!--
                $archived = $request->boolean('archived');
            -->

            Retrieving A Portion Of The Input Data
            If you need to retrieve a subset of the input data, you may use the    only
            and except methods. Both of these methods accept a singl array or a dynamic
            list of arguments:

            <!--
                $input = $request->only(['username', 'password']);

                $input = $request->only('username', 'password');

                $input = $request->except(['credit_card']);

                $input = $request->except('credit_card');
            -->

            <NOTE>
                The only method returns all of the key / value pairs that
                you request; however, it will not return key / value pairs
                that are not present on the request.
            </NOTE>

            Determining If An Input Value Is Present
            You should use the has method to determine if a value is present
            on the request. The has method returns true if the value      is
            present on the request:

            <!--
                if ($request->has('name')) {
                    //
                }
            -->

            When given an array, the has method will determine if
            all of the specified values are present:

            <!--
                if ($request->has(['name', 'email'])) {
                    //
                }
            -->

            The hasAny method return true if any of the specified
            values are present:

            <!--
                if ($request->hasAny(['name', 'email'])) {
                    //
                }
            -->

            If you would like to determine if a value is present on
            the request and is not empty, you may use the    filled
            method:

            <!--
                if ($request->filled('name')) {
                    //
                }
            -->

            To determine if a given key is absent from the request,
            you may use the missing method:

            <!--
                if ($request->missing('name')) {
                    //
                }
            -->
            <oldInput>
                Laravel allows you to keep input from one request during the next  request.
                This feature is particularly useful for re-populating forms after detecting
                validation errors. However, if you are using Laravel's included  validation
                features it is unlikely you will need to manually use these methods as some
                of Laravel's built-in validation facilities will call them automatically.

                Flashing Input To The Session
                The flash method on the Illuminate\Http\Request class will flash the current
                input to the session so that it is available during the user's next  request
                to the application:

                <!--
                    $request->flash();
                -->

                You may also use the flashOnly and flashExcept methods to flash a subset     of
                the request data to the session. These methods are useful for keeping sensitive
                information such as passwords out of the session:

                <!--
                    $request->flashOnly(['username', 'email']);
                    $request->flashExcept(['password']);
                -->

                Flashing Input Then Redirecting
                Since you often will want to flash input to the session and then redirect to
                the previous page, you may easily chain input flashing onto a redirect using
                th withInput method:

                <!--
                    return redirect('form')->withInput();

                    return redirect('form')->withInput(
                        $request->except('password');
                    );
                -->

                Retrieving Old Input
                To retrieve flashed input from the previous request, use the old  method
                on the Request instance. The old method will pull the previously flashed
                input data from the session:

                <!--
                    $username = $request->old('username');
                -->

                Laravel also provides a global old helper. If you are displaying old
                input within a Blade template, it is more convenient to use the  old
                helper. If no old input exists for the given field, null will     be
                returned:

                <!--
                    <input type="text" name="username" value="{{  old('username')  }}">
                -->

            </oldInput>
            <cookies>
                Retrieving Cookies From Requests
                All cookies created by the Laravel framework are encrypted and
                signed with an authentication code, meaning they will       be
                considered invalid if they have been changed by client.     To
                retrieve a cookie value from the request use the cookie method
                on a Illuminate\Http\Request instance:

                <!--
                    $value = $request->cookie('name');
                -->

                Alternatively, you may use the Cookie facade to access cookie
                values:

                <!--
                    use Illuminate\Support\Facades\Cookie;

                    $value = Cookie::get('name');
                -->

                Attaching Cookies To Responses
                You may attach a cookie to an outgoing Illuminate\Http\Response instance using
                the cookie method. You should pass the name, value, and number of minutes  the
                cookie should be considered valid to this method:

                <!--
                    return response('Hello world')->cookie(
                        'name', 'value', $minutes
                    );
                -->

                The cookie method also accepts a few more arguments which are used  less
                frequently. Generally, these arguments have the same purpose and meaning
                as the arguments that would be given to PHP's native setcookie method:

                <!--
                    return response('Hello World')->cookie(
                      'name', 'value', $minutes, $path, $domain, $secure, $httpOnly
                    );
                -->

                Alternatively, you can use the Cookie facade to "queue" cookies  for
                attachment to the outgoing response from your application. The queue
                method accepts a Cookie instance or the arguments needed to create a
                Cookie instance. These cookies will be attached to the      outgoing
                response before it is sent to the browser:

                <!--
                    Cookie::queue(Cookie::make('name', 'value', $time));

                    Cookie::queue('name', 'value', $time);
                -->

                Generating Cookie Instances
                If you would like to generate a Symfony\Component\HttpFoundation\Cookie
                instance that can be given to a response instance at a later time,  you
                may use the global cookie helper. This cookie will not be sent back  to
                the client unless it is attached to a response instance:

                <!--
                    $cookie = cookie('name', 'value', $time);

                    return response('hello world')->cookie($cookie);
                -->
            </cookies>
        </retrievingInput>
        <files>
            <retrievingUploadedFiles>
                You may access uploaded files from a Illuminate\Http\Request instance
                using the file method or using dynamic properties. The file    method
                returns an instance of the Illuminate\Http\UploadedFile class,  which
                extends the PHP SplFileInfo class and provides a variety of   methods
                for interacting with the file:

                <!--
                    $file = $request->file('photo');

                    $file = $request->photo;
                -->

                You may determine if a file is present on the request
                using the hasFile method:

                <!--
                    if ($request->hasFile('photo')) {
                        //
                    }
                -->

                Validating Successful Uploads
                In addition to checking if the file is present, you may verify that
                there were no problems uploading the file via the isValid method:

                <!--
                    if ($request->file('photo')->isValid();
                -->

                File Paths & Extensions
                The UploadedFile class also contains methods for accessing the file's
                fully-qualified path and its extensions. The extension method    will
                attempt to guess the file's extension based on its contents.     This
                extension may be different from the extension that was supplied    by
                the client:

                <!--
                    $path = $request->photo->path();

                    $extension = $request->photo->extension();
                -->

                Other File Methods
                There are a variety of the methods available on UploadedFile instances. Check out the   API documentation
                at https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/HttpFoundation/File/UploadedFile.php
                for the class for more information regrading these methods.
            </retrievingUploadedFiles>
            <storingUploadedFiles>
                To store an uploaded file, you will typically use one of your configured filesystems.
                The UploadedFile class has a store method which will move an uploaded file to one  of
                your disks, which may be a location on your local filesystem or even a cloud  storage
                location like Amazon s3.

                The store method accepts the path where the file should be stored relative to      the
                filesystem's configured root directory. This path should not contain a file name since
                a unique ID will automatically be generated to serve as the file name.

                The store method also accepts an optional second argument for the name of the disk that
                should be used to store the file. The method will return the path of the file  relative
                to the disk's root:

                <!--
                    $path = $request->photo->store('images');

                    $path = $request->photo->store('images', 's3);
                -->

                If you don't want a file name to be automatically generated, you may use the storeAs
                method, which accepts the path, file name, and disk name as its arguments:

                <!--
                    $path = $request->photo->storeAs('images', 'filename.jpg');

                    $path = $request->photo->storeAs('images', 'filename.jpg', 's3');
                -->
            </storingUploadedFiles>
        </files>
        <configuringTrustedProxies>
            When running your applications behind a load balancer that terminates TLS / SSL
            certificates, you may notice your application sometimes does not generate HTTPS
            links. Typically this is because your application is being forwarded    traffic
            from your load balancer on port 80 and does not know it should generate  secure
            links.

            To solve this, you may use the App\Http\Middleware\TrustProxies middleware that
            is included in your Laravel application, which allows you to quickly  customize
            the load balancer's or proxies that should be trusted by your application. Your
            trusted proxies should be listed as an array on the $proxies property of   this
            middleware. In addition to configuring the trusted proxies, you may   configure
            the proxy $headers that should be trusted:

            <!--
                namespace App\Http\Middleware;

                use Fideloper\Proxy\TrustProxies as Middleware;
                use Illuminate\Http\Request;

                class TrustProxies extends Middleware
                {
                    /**
                     * The trusted proxies for this application.
                     *
                     * @var string|array
                     */
                    protected $proxies = [
                        '192.168.1.1',
                        '192.168.1.2',
                    ];

                    /**
                     * The headers that should be used to detect proxies.
                     *
                     * @var string
                     */
                    protected $headers = Request::HEADER_X_FORWARDED_ALL;
                }
            -->

            <NOTE>
                If you are using AWS Elastic Load Balancing, your $headers value   should be
                Request::HEADER_X_FORWARDED_AWS_ELB. For more information on the   constants
                that may be used in the $headers property, check out Symfony's documentation
                on trusting proxies. https://symfony.com/doc/current/deployment/proxies.html
            </NOTE>

            Trusting All Proxies
            If you are using Amazon AWS or another "cloud" load balancer provider, you     may
            not know the IP addresses of your actual balancers. In this case, you may use * to
            trust all proxies:

            <!--
                /**
                 * The trusted proxies for this application.
                 *
                 * @var string|array
                 */
                protected $proxies = '*';
            -->
        </configuringTrustedProxies>
    </requests>

    <responses>
        <creatingResponses>
            Strings & Arrays
            All routes and controllers should return a response to be sent back to   the
            user's browser. Laravel provides several different ways to return responses.
            The most basic response is returning a string from a route or    controller.
            The framework will automatically convert the string into a full         HTTP
            response:

            <!--
                Route::get('/', function () {
                    return 'hello world';
                });
            -->

            In addition to returning strings from your routes and controller, you
            may also return arrays. The framework will automatically convert  the
            array into a JSON response:

            <!--
                Route::get('/', function () {
                    return [1, 2, 3];
                });
            -->

            <NOTE>
                Did you know you can also return Eloquent collections from
                your routes or controllers? They will automatically     be
                converted to JSON. Give it a shot!
            </NOTE>

            Response Objects
            Typically, you won't just be returning simple strings or arrays from  your
            route actions. Instead, you will be returning ful Illuminate\Http\Response
            instances or views. Returning a full Response instance allows you       to
            customize the response's HTTP status code and headers. A Response instance
            inherits from the Symfony\Component\HttpFoundation\Response class,   which
            provides a variety of methods for building HTTP responses:

            <!--
                Route::get('home', function () {
                    return response('Hello World', 200)
                                ->header('Content-Type', 'text/plain');
                });
            -->

            <attachingHeadersToResponses>
                Keep in mind that most response methods are chainable, allowing for the
                fluent construction of reponse instances. For example, you may use  the
                header method to add a series of headers to teh response before sending
                it back to the user:

                <!--
                    return response($content)
                                ->header('Content-Type', $type)
                                ->header('X-Header-One', 'Header Value')
                                ->header('X-Header-Two', 'Header Value');
                -->

                Or, you may use the withHeaders method to specify an array of headers
                to be added to the response:

                <!--
                    return response($content)
                                ->withHeaders([
                                    'Content-Type' => $type,
                                    'X-Header-One' => 'Header Value',
                                    'X-Header-Two' => 'Header Value',
                                ]);
                -->

                Cache Control Middleware
                Laravel includes a cache.headers middleware, which may be used to quickly
                set the Cache-Control header for a group of routes. If etag is  specified
                in the list of directives, and MD5 hash of the response content      will
                automatically be set as the ETag identifier:

                <!--
                    Route::middleware('cache.headers:public;max_age=2628000;etag')->group(function () {
                       Route::get('privacy', function () {
                            // ...
                       });

                       Route::get('terms', function() {
                           // ...
                       });
                    });
                -->

            </attachingHeadersToResponses>
            <attachingCookiesToResponses>
                The cookie method on response instances allows you to easily attach cookies to
                the response. For example, you may use the cookie method to generate a  cookie
                and fluently attach it to the response instance like so:

                <!--
                    return response($content)
                                    ->header('Content-Type', $type)
                                    ->cookie('name', 'value', $minutes);
                -->

                The cookie method also accepts a few more arguments which are used less frequently.
                Generally, these arguments have the same purpose and meaning as the arguments  that
                would be given to PHP's native setcookie method:

                <!--
                    ->cookie($name, $value, $minutes, $path, $domain, $secure, $httpOnly)
                -->

                Alternatively, you can use the Cookie facade to "queue" cookies for attachment  to the
                outgoing response from your application. The queue method accepts a Cookie    instance
                or the arguments needed to create a Cookie instance. These cookies will be attached to
                the outgoing response before it is sent to the browser:

                <!--
                    Cookie::queue(Cookie::make('name', 'value', $minutes));

                    Cookie::queue('name', 'value', $minutes);
                -->

            </attachingCookiesToResponses>
            <CookiesAndEncryption>
                By default, all cookies generated by Laravel are encrypted and signed so that
                they can't be modified or read by the client. If you would like to    disable
                encryption for a subset of cookies generated by your application, you may use
                the $except property of the App\Http\Middleware\EncryptCookies     middleware
                which is located in the app/Http/Middleware directory:

                <!--
                    /**
                     * The names of the cookies that should not be encrypted.
                     *
                     * @var array
                     */
                    protected $except = [
                        'cookie_name',
                    ];
                -->
            </CookiesAndEncryption>
        </creatingResponses>
        <redirects>
            Redirect responses are instances of the Illuminate\Http\RedirectResponse class, and
            contain the proper headers needed to redirect the user to another URL. There    are
            several ways to generate a RedirectResponse instance. The simplest method is to use
            the global redirect helper:

            <!--
                Route::get('dashboard', function () {
                    return redirect('home/dashboard');
                });
            -->

            Sometimes you may wish to redirect the user to their previous location, such as when
            a submitted form is invalid. You may do so by using the global back helper function.
            Since this feature utilizes the session, make sure the route calling the        back
            function is using the web middleware group or has all of the session      middleware
            applied:

            <!--
                Route::post('user/profile', function () {
                    return back()->withInput();
                });
            -->

            <redirectingToNamedRoutes>
                When you call the redirect helper with no parameters, an instance of
                Illuminate\Routing\Redirector is returned, allowing you to call  any
                method on the Redirector instance. For example, to generate        a
                RedirectResponse to a named route, you may use the route method:

                <!--
                    return redirect()->route('login');
                -->

                If your route has parameters, you may pass them as the second
                argument to the route method:

                <!--
                    // For a route with the following URI: profile/{id}

                    return redirect()->route('profile', ['id' => 1]);
                -->

                Populating Parameters Via Eloquent Models
                If you are redirecting to a route with an "ID" parameter that is being
                populated from an Eloquent model, you may pass the model itself.   The
                ID will be extracted automatically:

                <!--
                    // For a route with the following URI: profile/{id}

                    return redirect()->route('profile', [$user]);
                -->

                If you would like to customize the value that is placed in the route parameter,
                you should override the getRouteKey method on your Eloquent model:

                <!--
                    /**
                     * Get the value of the model's route key.
                     *
                     * @return mixed
                     */
                    public function getRouteKey()
                    {
                        return $this->slug;
                    }
                -->

            </redirectingToNamedRoutes>
            <redirectingToControllerActions>
                You may also generate redirects to controller actions. To do so, pass the controller
                and action name to the action method. Remember, you do not need to specify the  full
                namespace to the controller since Laravel's RouteServiceProvider will  automatically
                set the base controller namespace:

                <!--
                    return redirect()->action('HomeController@index');
                -->

                If your controller route requires parameters, you may pass them as
                the second argument to the action method:

                <!--
                    return redirect()->action(
                        'UserController@profile', ['id' => 1]
                    );
                -->

            </redirectingToControllerActions>
            <redirectingToExternalDomains>
                Sometimes you may need to redirect to a domain outside of your application.
                You may do so by calling the away method, which creates a  RedirectResponse
                without any additional URL encoding, validation, or verification:

                <!--
                    return redirect()->away('getRouteKey');
                -->

            </redirectingToExternalDomains>
            <redirectingWithFlashedSessionData>
                Redirecting to a new URL and flashing data to the session are usually done    at
                the same time. Typically, this is done after successfully performing on   action
                when you flash a success message to the session. For convenience, you may create
                a RedirectResponse instance and flash data to the session in a single,    fluent
                method chain:

                <!--
                    Route::post('user/profile', function () {
                        // Update the user's profile

                        return redirect('dashboard')->with('status', 'Profile updated!');
                    });
                -->

                After the user is redirected, you may display the flashed message from
                the session. For example, using Blade syntax:

                <!--
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{  session('status') }}
                        </div>
                    @endif
                -->
            </redirectingWithFlashedSessionData>
        </redirects>
        <otherResponseTypes>
            The response helper may be used to generate other types of response instances. When
            the response helper is called without arguments, an implementation of           the
            Illuminate\Contracts\Routing\ResponseFactory contract is returned. This    contract
            provides several helpful methods for generating responses.

            <viewResponses>
                If you need control over the response's status and headers but also need to return
                a view as the response's content, you should use the view method:

                <!--
                    return response()->view('hello', $data, 200)->header('Content-Type', $type);
                -->
            </viewResponses>
            <JSONResponses>
                The json method will automatically set the Content-Type header to application/json,
                as well as convert the given array to JSON using the json_encode PHP function:

                <!--
                    return response()->json([
                        'name' => 'omar',
                        'state' => 'CA',
                    ]);
                -->

                If you would like to create a JSONP response, you may use the json method in
                combination with the withCallback method:

                <!--
                    return response()
                                ->json(['name' => 'Abigail', 'state' => 'CA'])
                                ->withCallback($request->input('callback'));
                -->
            </JSONResponses>
            <fileDownloads>
                The download method may be used to generate a response that forces the user's
                browser to download the file at the given path. The download method accepts a
                file name as the second argument to the method, which will determine the file
                name that is seen by the user downloading the file. Finally, you may pass  an
                array of HTTP headers as the third argument to the method:

                <!--
                    return response()->download($pathToFile);

                    return response()->download($pathToFile, $fileName, $headers);

                    return response()->download($pathToFile)->deleteFileAfterSend();
                -->

                <NOTE>
                    Symfony HttpFoundation, which manages file downloads, requires the
                    file being downloaded to have an ASCII file name.
                </NOTE>

                Streamed Downloads
                Sometimes you may wish to turn the string response of a given operation      into
                a downloadable response without having to write the contents of the     operation
                to disk. You may use the streamDownload method in this scenario. This      method
                accepts a callback, file name, and an optional array of headers as its arguments:

                <!--
                    return response()->streamDownload(function () {
                        echo GitHub::api('repo')
                                        ->contents()
                                        ->readme('laravel', 'laravel')['contents'];
                    }, 'laravel-readme.md');
                -->

            </fileDownloads>
            <fileResponses>
                The file method may be used to display a file, such as an image or PDF,
                directly in the user's browser instead of initiating a download.   This
                method accepts the path to the file as its first argument and an  array
                of headers as its second argument:

                <!--
                    return response()->file($pathFile);

                    return response()->file($pathFile, $headers);
                -->
            </fileResponses>
        </otherResponseTypes>
        <responseMacros>
            If you would like to define a custom response that you can re-use in a variety
            of your routes and controllers, you may use the macro method on the   Response
            facade. For example, from a service provider's boot method:

            <!--
                namespace App\Providers;

                use Illuminate\Support\Facades\Response;
                use Illuminate\Support\ServiceProvider;

                class ResponseMacroServiceProvider extends ServiceProvider
                {
                    /**
                     * Register the application's response macros.
                     *
                     * @return void
                     */
                    public function boot()
                    {
                        Response::macro('caps', function ($value) {
                            return Response::make(strtoupper($value));
                        });
                    }
                }
            -->

            The macro function accepts a name as its first argument, and a Closure as its   second.
            The macro's Closure will be executed when calling the macro name from a ResponseFactory
            implementation or the response helper:

            <!--
                return response()->caps('foo');
            -->
        </responseMacros>
    </responses>
    <views>
        <creatingViews>
            <NOTE>
                Looking for more information on how to write Blade templates ?
                Check out the full Blade documentation to get started.
                <a href="https://laravel.com/docs/6.x/blade"></a>
            </NOTE>
            Views contain the HTML served by your application and separate  your
            controller / application logic from your presentation logic.   Views
            are store in the resources/views directory. A simple view might look
            something like this:

            <!--
                 View stored in resources/views/greeting.blade.php

            <html>
                <body>
                    <h1>Hello, {{ $name }}</h1>
                </body>
            </html>
            -->

            Since this view is stored at resources/views/greeting.blade.php, we may
            return it using the global view helper like so:

            <!--
                Route::get('/', function () {
                    return view('greeting', ['name' => 'James']);
                });
            -->

            As you can see, the first argument passed to the view helper corresponds    to
            the name of the view file in the resources/view directory. The second argument
            is an array of data that should be made available to the view. In this case we
            are passing the name variable, which is displayed in the view using      Blade
            syntax. Views may also be nested within subdirectories of the  resources/views
            directory. "Dot" notation may be used to reference nested views. For  example,
            if your view is stored at resources/views/admin/profile.blade.php, you     may
            reference it like so:

            <!--
                return view('admin.profile', $data);
            -->

            <NOTE>
                View directory names should not contain the . character.
            </NOTE>

            Determining If A View Exists
            If you need to determine if a view exists, you may use the View facade.
            The exists method will return true if the view exists:

            <!--
                if(View::exists('emails.customer')) {
                    //
                }
            -->

            Creating The First Available View
            Using the first method, you may create the first view that exists in a given
            array of views This is useful if your application or package allows views to
            be customized or overwritten:

            <!--
                return view()->first(['custom.admin', 'admin'], $data);
            -->

            You may also call this method via the View facade:

            <!--
                use Illuminate\Support\Facades\View;

                return View::first(['custom.admin', 'admin'], $data);
            -->
        </creatingViews>
        <passingDataToViews>
            As you saw in the previous examples, you may pass an array of data to views:

            <!--
                return view('greetings', $data);
            -->

            When passing information in this manner, the data should be an array with
            key / value pairs. Inside your view, you can then access each value using
            its corresponding key, such as "echo $key". As an alternative to  passing
            a complete array of data to the view helper function you may use the with
            method to add individual pieces of data to the view:

            <!--
                return view('greeting')->with('name', 'Victoria');
            -->

            <sharingDataWithAllViews>
                Occasionally, you may need to share a piece of data with all views that are rendered
                by your application. You may do so using the view facade's share method.  Typically,
                you should place calls to share within a service provider's boot method You are free
                to add them to the AppServiceProvider or generate a separate serrvice  provider   to
                house them:

                <!--

                    namespace App\Providers;

                    use Illuminate\Support\Facades\View;

                    class AppServiceProvider extends ServiceProvider
                    {
                        /**
                         * Register any application services.
                         *
                         * @return void
                         */
                        public function register()
                        {
                            //
                        }

                        /**
                         * Bootstrap any application services.
                         *
                         * @return void
                         */
                        public function boot()
                        {
                            View::share('key', 'value');
                        }
                    }
                -->
            </sharingDataWithAllViews>
        </passingDataToViews>
        <viewComposers>
            View composers are callbacks or class methods that are called when a   view
            is rendered. If you have data that you want to be bound to a view each time
            that view is rendered a view composer can help you organize that logic into
            a single location.

            For this example, let's register the view composers within a service provider.    We'll
            use the view facade to access the underlying Illuminate\Contracts\View\Factory contract
            implementation. Remember, Laravel does not include a default directory for         view
            composers. You are free to organize them however you wish. For example, you cold create
            an app/Http/View/Composers directory:

            <!--

                namespace App\Providers;

                use Illuminate\Support\Facades\View;
                use Illuminate\Support\ServiceProvider;

                class ViewServiceProvider extends ServiceProvider
                {
                    /**
                     * Register any application services.
                     *
                     * @return void
                     */
                    public function register()
                    {
                        //
                    }

                    /**
                     * Bootstrap any application services.
                     *
                     * @return void
                     */
                    public function boot()
                    {
                        // Using class based composers...
                        View::composer(
                            'profile', 'App\Http\View\Composers\ProfileComposer'
                        );

                        // Using Closure based composers...
                        View::composer('dashboard', function ($view) {
                            //
                        });
                    }
                }
            -->

            <NOTE>
                Remember, if you create a new service provider to contain your  view
                composer registrations, you will need to add the service provider to
                the providers array in the config/app.php configuration file.
            </NOTE>

            Now that we have registered the composer, the ProfileComposer@compse method
            will be executed each time the profile view is being rendered. So,    let's
            define the composer class:

            <!--
                namespace App\Http\View\Composers;

                use App\Repositories\UserRepository;
                use Illuminate\View\View;

                class ProfileComposer
                {
                    /**
                     * The user repository implementation.
                     *
                     * @var UserRepository
                     */
                    protected $users;

                    /**
                     * Create a new profile composer.
                     *
                     * @param  UserRepository  $users
                     * @return void
                     */
                    public function __construct(UserRepository $users)
                    {
                        // Dependencies automatically resolved by service container...
                        $this->users = $users;
                    }

                    /**
                     * Bind data to the view.
                     *
                     * @param  View  $view
                     * @return void
                     */
                    public function compose(View $view)
                    {
                        $view->with('count', $this->users->count());
                    }
                }
            -->

            Just before the view is rendered, the composer's compose method  is
            called with the Illuminate\View\View instance. You may use the with
            method to bind data to the view.

            <NOTE>
                All view composers are resolved via the service container, so you
                may type-hint any dependencies you need within a       composer's
                constructor.
            </NOTE>

            Attaching A Composer To Multiple Views

            You may attach a view composer to multiple views att once by passing
            an array of views as the first argument to the composer method:

            <!--
                View::composer(
                    ['profile', 'dashboard'],
                    'App\Http\View\Composers\MyViewComposer'
                );
            -->

            The composer method also accepts the * character as a wildcard,
            allowing you to attach a composer to all views:

            <!--
                View::composer('*', function ($view) {
                    //
                });
            -->

            View Creators

            View creator are very similar to view composers; however, they are  executed
            immediately after the view is instantiated instead of waiting until the view
            is about to render. To register a view creator use the creator method:

            <!--
                View::creator('profile', 'App\Http\View\Creators\ProfileCreator');
            -->
        </viewComposers>
    </views>

    <URLGeneration>
        <introduction>
            Laravel provides several helpers to assist you in generating URLs for your application.
            These are mainly helpful when building links in you templates and API responses or when
            generating redirect responses to another part of your application.
        </introduction>
        <theBasics>
            <generatingBasicURLs>
                The url helper may be used to generate arbitrary URLs for your application. The generated
                URL will automatically use the scheme (HTTP or HTTPS) and host from the current request:

                <!--
                    $post = App\Post::find(1);

                    // http://eample.com/posts/1
                    echo url("/posts/{$post->id}");

                    // http://eample.com/omar
                    echo url("omar");

                -->
            </generatingBasicURLs>
            <accessingTheCurrentURL>
                If no path is provided to the url helper, a Illuminate\Routing\UrlGenerator
                instance is returned, allowing you to access information about the  current
                URL:

                <!--
                    // Get the current URL without the query string...
                    echo url()->current();

                    // Get the current URL including the query string...
                    echo url()->full();

                    // Get the full URL for the previous request...
                    echo url()->previous();
                -->

                Each of these methods may also be accessed via the URL facade:

                <!--
                    use Illuminate\Support\Facades\URL;

                    echo URL::current();
                -->
            </accessingTheCurrentURL>
        </theBasics>
        <URLsForNamedRoutes>
            The route helper may be used to generate URLs to named routes. Named routes allow
            you to generate URLs without being coupled to the actual URL defined on the    route.
            Therefore, if the route's URL changes, no changes need to be made to your       route
            function calls. For example, imagine your application contains a route defined   like
            the following:

            <!--
                Route::get('/post/{post}', function () {
                    //
                })->name('post.show');
            -->

            To generate a URL to this route, you may use the route helper like so:

            <!--
                // http://example.com/post/1
                echo route('post.show', ['post' => 1]);
            -->

            You will often be generating URLs using teh primary key of Eloquent models. For
            this reason, you may pass Eloquent models as parameter values. The route helper
            will automatically extract the model's primary key:

            <!--
                echo route('post.show', ['post' => $post]);
            -->

            The route helper may also be used to generate URLs for routes
            with multiple parameters:

            <!--
                Route::get('/post/{post}/comment/{comment}', function () {
                    //
                })->name('comment.show');

                echo route('comment.show', ['post' => 1, 'comment' => 3]);

                // http://example.com/post/1/comment/3
            -->

            <signedURLs>
               Laravel allows you to easily create "signed" URLs to named routes. These URLs
                have a "signature" hash appended to the query string which allows Laravel to
                verify that the URL has not been modified since it was created. Signed  URLs
                are especially useful for routes that are publicly accessible yet need     a
                layer of protection against URL manipulation. For example, you might     use
                signed URLs to implement a public "unsubscribe" link that is emailed to your
                customers. To create a signed URL to a named route use the       signedRoute
                method of the URL facade:

                <!--
                    use Illuminate\Support\Facades\URL;

                    return URL::signedRoute('unsubscribe', ['user' => 1]);
                -->

                If you would like to generate a temporary signed route URL that expires,
                you may use the temporarySignedRoute method:

                <!--
                    use Illuminate\Support\Facades\URL;

                    return URL::temporarySignedRoute(
                        'unsubscribe', now()->addMinutes(30), ['user' => 1]
                    );
                -->

                Validating Signed Route Requests

                To verify that an incoming request has a valid signature, you should call the
                hasValidSignature method on the incoming Request:

                <!--
                    use Illuminate\Http\Request;

                    Route::get('/unsubscribe/{user}', function (Request $request) {
                        if (! $request->hasValidSignature()) {
                            abort(401);
                        }

                        // ...
                    })->name('unsubscribe');
                -->

                Alternatively, you may assign the Illuminate\Routing\Middleware\ValidateSignature
                middleware to the route. If it is not already present, you should assign     this
                middleware a key in your HTTP kernel's routeMiddleware array:

                <!--
                    /**
                     * The application's route middleware.
                     *
                     * These middleware may be assigned to groups or used individually.
                     *
                     * @var array
                     */
                    protected $routeMiddleware = [
                        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
                    ];
                -->

                Once you have registered the middleware in your kernel, you may attach it to a route.
                If the incoming request does not have a valid signature, the middleware          will
                automatically return a 403 error response:

                <!--
                    Route::post('/unsubscribe/{user}', function (Request $request) {
                        // ...
                    })->name('unsubscribe')->middleware('signed');
                -->
            </signedURLs>
        </URLsForNamedRoutes>
        <URLsForControllerActions>
            The action function generates a URL for the given controller action. You do
            not need to pass the full namespace of the controller. Instead, pass    the
            controller class name relative to the App\Http\Controllers namespace:

            <!--
                $url = action('HomeController@index');
            -->

            You may also reference actions with a "callable" array syntax:

            <!--
                use App\Http\Controllers\HomeController;

                $url = action([HomeController::class, 'index']);
            -->

            If the controller method accepts route parameters, you may pass
            them as the second argument to the function:

            <!--
                $url = action('UserController@profile', ['id' => 1]);
            -->

        </URLsForControllerActions>
        <defaultValues>
            For some applications, you may wish to specify request-wide default values for
            certain URL parameters. For example, imagine many of your routes        define
            a {locale} parameter:

            <!--
                Route::get('/{locale}/posts', function () {
                    //
                })->name('post.index');
            -->

            It is cumbersome to always pass the locale every time you call the route helper.
            So, you may use the URL::defaults method to define a default value for      this
            parameter that will always be applied during the current request. You may   wish
            to call this method from a route middleware so that you have access to       the
            current request:

            <!--

                namespace App\Http\Middleware;

                use Closure;
                use Illuminate\Support\Facades\URL;

                class SetDefaultLocaleForUrls
                {
                    public function handle($request, Closure $next)
                    {
                        URL::defaults(['locale' => $request->user()->locale]);

                        return $next($request);
                    }
                }
            -->

            Once the default value for the locale parameter has been set, you are
            no longer required to pass its value when generating URLs via     the
            route helper.

        </defaultValues>
    </URLGeneration>

    <session>
        <introduction>
            Since HTTP driven applications are stateless, sessions provide a way to store information
            about the user across multiple requests. Laravel ships with a variety of session backends
            that are accessed through an expressive, unified API Support for popular backends such as
            Memcached, Redis, and databases is included out of the box.

            <configuration>
                The session configuration file is stored at config/session.php. Be sure to review   the
                options available to you in this file. By default, Laravel is configured to use     the
                file session driver, which will work well for many applications. The session     driver
                configuration option defines where session data will be stored for each request Laravel
                ships with several great drivers out of the box:

                1- file: sessions are stored in storage/framework/sessions.
                2- cookie: sessions are stored in secure, encrypted cookies.
                3- database: sessions are stored in a relational database.
                4- memcached: session are stored in one of these fast, cache based stores.
                5- redis: session are stored in one of these fast, cache based stores.
                6- array: sessions are stored in a PHP array and will not be persisted.

                <NOTE>
                    The array driver is used during testing and prevents the data stored
                    in the session from being persisted.
                </NOTE>
            </configuration>
            <driverPrerequisites>
                When using the database session driver, you will need to create a  table
                to contain the session items. Below is an example Schema declaration for
                the table:

                <!--
                    Schema::create('sessions', function ($table) {
                        $table->string('id')->unique();
                        $table->unsignedInteger('user_id')->nullable();
                        $table->string('ip_address', 45)->nullable();
                        $table->text('user_agent')->nullable();
                        $table->text('payload');
                        $table->integer('last_activity');
                    });
                -->

                You may use the session:table Artisan command to generate this migration:

                <!--
                    php artisan session:table
                    php artisan migrate
                -->

                Redis
                Before using Redis sessions with Laravel, you will need to either install the
                PhpRedis PHP extension via PECL or install the predis/predis    package(~1.0)
                via Composer. For more information on configuring Redis, consult its  Laravel
                documentation page.

                <NOTE>
                    In the session configuration file, the connection option may be used to
                    specify which Redis connection is used by the session.
                </NOTE>
            </driverPrerequisites>
        </introduction>
        <usingTheSession>
            <retrievingData>
                There are two primary ways of working with session data in Laravel: teh global
                session helper and via Request instance. First, let's look at accessing    the
                session via a Request instance, which can be type-hinted on a       controller
                method. Remember, controller method dependencies are automatically    injected
                via the Laravel service container:

                <!--
                    namespace App\Http\Controllers;

                    use App\Http\Controllers\Controller;
                    use Illuminate\Http\Request;

                    class UserController extends Controller
                    {
                        /**
                         * Show the profile for the given user.
                         *
                         * @param  Request  $request
                         * @param  int  $id
                         * @return Response
                         */
                        public function show(Request $request, $id)
                        {
                            $value = $request->session()->get('key');

                            //
                        }
                    }
                -->

                When you retrieve an item from the session, you may also pass a default value a
                the second argument to the get method. This default value will be returned   if
                the specified key does not exist in teh session. If you pass a Closure as   the
                default value to the get method and the requested key does not exist,       the
                Closure will be executed and its result returned:

                <!--
                    $value = $request->session()->get('key', 'default');

                    $value = $request->session()->get('key', function () {
                        return 'default';
                    });
                -->

                The Global Session Helper

                You may also use the global session PHP function to retrieve and store data in
                the session. When the session helper is called with a single, string  argument,
                it will return the value of that session key. When the helper is called with an
                array of key / value pairs, those values will be stored in the session:

                <!--
                    Route::get('home', function () {
                        // Retrieve a piece of data from the session...
                        $value = session('key');

                        // Specifying a default value...
                        $value = session('key', 'default');

                        // Store a piece of data in the session...
                        session(['key' => 'value']);
                    });
                -->

                <NOTE>
                    There is little practical difference between using the session via an HTTP
                    request instance versus using the global session helper. Both methods  are
                    testable via the assertSessionHas method which is available in all of your
                    test cases.
                </NOTE>

                Retrieving All Session Data

                If you would like to retrieve all the data in the session, you may use the all method:

                <!--
                    $data = $request->session()->all();

                    // OR

                    $data = session()->all();
                -->

                Determining If An Item Exists In The Session

                To determine if an item is present in the session, you may use the has method.
                The has method returns true if the item is present and if it is not null:

                <!--
                    if($request()->session()->has('users')) {
                        //
                    }
                -->

                To determine if an item is present in the session, even if its value
                is null, you may use the exists method.

                <!--
                    if($request->session()->exists('users')) {
                        //
                    }
                -->
            </retrievingData>
            <storingData>
                To store data in the session, you will typically use the put
                method or the session helper:

                <!--
                    // Via a request instance...
                    $request->session()->put('key', 'value');

                    // Via the global helper...
                    session(['value' => 'value']);
                -->

                Pushing To Array Session Values

                The push method may be used to push a new value onto a session value that is
                an array. For example, if the user.teams key contains an array of team names
                you may push a new value onto the array like so:

                <!--
                    $request->session()->push('user.teams', 'developers');
                -->

                Retrieving & Deleting An Item

                The pull method will retrieve and delete an item from the session
                in a single statement:

                <!--
                    $value = $request->session()->pull('key', 'default');
                -->
            </storingData>
            <flashData>
                Sometimes you may wish to store items in the session only for the next    request.
                You may do so using the flash method. Data stored in the session using this method
                will be available immediately and during the subsequent HTTP request. After    the
                subsequent HTTP request, the flashed data will be deleted. Flash data is primarily
                useful for short-lived status messages:

                <!--
                    $request->session()->flash('status', 'task was successful!');
                -->

                If you need to keep your flash data around for several requests, you may use the reflash
                method, which will keep all of the flash data for an additional request. If you     only
                need to keep specific flash data, you may use the keep method:

                <!--
                    $request->session()->reflash();

                    $request->session()->keep(['username', 'email']);
                -->
            </flashData>
            <deletingData>
                The forget method will remove a piece of data from the session. If you would like to
                remove all data from the session, you may use the flush method:

                <!--
                    // Forget a single key...
                    $request->session()->forget('key');

                    // Forget multiple keys...
                    $request->session()->forget(['key1', 'key2']);

                    $request->session()->flush();
                -->
            </deletingData>
            <regeneratingTheSessionID>
                Regenerating the session ID is often done in order to prevent malicious users from
                exploiting a session fixation attack on your application. Laravel    automatically
                regenerates the session ID during authentication if you are using the     built-in
                LoginController; however, if you need to manually regenerate the session ID,   you
                may use the regenerate method.

                <!--
                    $request->session()->regenerate();
                -->
            </regeneratingTheSessionID>
        </usingTheSession>
        <addingCustomSessionDrivers>
            <implementingTheDriver>
                Your custom session driver should implement the SessionHandlerInterface. This interface contains
                just a few simple methods we need to implement. A stubbed MongoDB implementation looks something
                like this:

                <!--

                    namespace App\Extensions;

                    class MongoSessionHandler implements \SessionHandlerInterface
                    {
                        public function open($savePath, $sessionName) {}
                        public function close() {}
                        public function read($sessionId) {}
                        public function write($sessionId, $data) {}
                        public function destroy($sessionId) {}
                        public function gc($lifetime) {}
                    }
                -->

                <NOTE>
                    Laravel does not ship with a directory to contain your extensions. You are
                    free to place them anywhere you like. In this example, we have created  an
                    Extensions directory to house the MongoSessionHandler.
                </NOTE>

                Since the purpose of these methods is not readily understandable, let's quickly
                cover what each of the methods do:

                1-  The open method would typically be used in file based session store  systems.
                    Since Laravel ships with a file session driver, you will almost never need    to
                    put anything in this method. You can leave it as an empty stub. I is a fact   of
                    poor interface design (which we'wll discuss later) that PHP requires us       to
                    implement this method.

                2-  The close method, like the open method, can also usually be disregarded. For most
                    drivers, it is not needed.

                3-  The read method should return the string version of the session data       associated
                    with the given $sessionId. There is no need to do any serialization or other encoding
                    when retrieving or storing session data in your driver, as Laravel will perform   the
                    serialization for you.

                4-  The write method should write the given $data string associated with the $sessionId to
                    some persistent storage system such as MongoDB, Dynamo, etc. Again, you should     not
                    perform any serialization Laravel will have already handled that for you.

                5-  The destroy method should remove the data associated with the $sessionId from persistent
                    storage.

                6-  The gc method should destroy all session data that is older than the given     $lifetime,
                    which is a UNIX timestamp. Fr self-expiring systems like Memcached and Redis, this method
                    may be left empty.
            </implementingTheDriver>
            <registeringTheDriver>
                Once your driver has been implemented, you are ready to register it with the framework. To add
                additional drivers to Laravel's session backend, you may use the extend method on the  Session
                facade. You should call the extend method from the boot method of a service provider.  You may
                do this from the existing AppServiceProvider or create an entirely new provider:

                <!--
                    namespace App\Providers;

                    use App\Extensions\MongoSessionHandler;
                    use Illuminate\Support\Facades\Session;
                    use Illuminate\Support\ServiceProvider;

                    class SessionServiceProvider extends ServiceProvider
                    {
                        /**
                         * Register any application services.
                         *
                         * @return void
                         */
                        public function register()
                        {
                            //
                        }

                        /**
                         * Bootstrap any application services.
                         *
                         * @return void
                         */
                        public function boot()
                        {
                            Session::extend('mongo', function ($app) {
                                // Return implementation of SessionHandlerInterface...
                                return new MongoSessionHandler;
                            });
                        }
                    }
                -->

                Once the session driver has been registered, you may use the mongo driver in your
                config/session.php configuration file.
            </registeringTheDriver>
        </addingCustomSessionDrivers>
    </session>
    <validation>
        <introduction>
            Laravel provides several different approaches to validate your application's incoming
            data By default, Laravel's base controller class uses a ValidatesRequests trait which
            provides a convenient method to validate incoming HTTP requests with a variety     of
            powerful validation rules.
        </introduction>
        <validationQuickstart>
            To learn about Laravel's powerful validation features, let's look at a complete example
            of validating a form and displaying the error back to the user.
            <definingTheRoutes>
                First, let's assume we have the following routes defined in our routes/web.php file:

                <!--
                    Route::get('post/create', 'PostController@create');

                    Route::post('post', 'PostController@store');
                -->

                The GET route will display a form for the user to create a new blog post, while the
                POST route will store teh new blog post in the database.
            </definingTheRoutes>
            <creatingTheController>
                Next, let's take a look at a simple controller that handles these routes We'll leave
                the store method empty for now:

                <!--
                    namespace App\Http\Controllers;

                    use App\Http\Controllers\Controller;
                    use Illuminate\Http\Request;

                    class PostController extends Controller
                    {
                        /**
                         * Show the form to create a new blog post.
                         *
                         * @return Response
                         */
                        public function create()
                        {
                            return view('post.create');
                        }

                        /**
                         * Store a new blog post.
                         *
                         * @param  Request  $request
                         * @return Response
                         */
                        public function store(Request $request)
                        {
                            // Validate and store the blog post...
                        }
                    }
                -->
            </creatingTheController>
            <writingTheValidationLogic>
                Now we are ready to fill in our store method with the logic to validate the new blog   post.
                To do this, we will use the validate method provided by the Illuminate\Http\Request  object.
                If the validation rules pass, your code will keep executing normally; however, if validation
                fails, an exception will be thrown and the proper error response will automatically be  sent
                back to the user. In the case of a traditional HTTP request, a redirect response will     be
                generated while a JSON response will be sent for AJAX requests. To get a better understanding
                of the validate method, let's jump back into the store method:

                <!--
                    /**
                     * Store a new blog post.
                     *
                     * @param  Request  $request
                     * @return Response
                     */
                    public function store(Request $request)
                    {
                        $validatedData = $request->validate([
                            'title' => 'required|unique:posts|max:255',
                            'body' => 'required',
                        ]);

                        // The blog post is valid...
                    }
                -->

                As you can see, we pass the desired validation rules into the validate method. Again,
                if the validation fails, the proper response will automatically be generated. If  the
                validation passes, our controller will continue executing normally.    Alternatively,
                validation rules may be specified as arrays of rules instead of a single |  delimited
                string:

                <!--
                    $validatedData = $request->validate([
                        'title' => ['required', 'unique:posts', 'max:255'],
                        'body' => ['required'],
                    ]);
                -->

                If you would like to specify the error bag in which the error messages should be
                placed, you may use the validateWithBag method:

                <!--
                    $request->validateWithBag('blog', [
                        'title' => ['required', 'unique:posts', 'max:255'],
                        'body' => ['required'],
                    ]);
                -->

                Stopping On First Validation Failure

                Sometimes you may wish to stop running validation rules on an attribute after the first
                validation failure. To do so, assign the bail rule to the attribute:

                <!--
                    $request->validate([
                        'title' => 'bail|required|unique:posts|max:255',
                        'body' => 'required',
                    ]);
                -->

                In this example, if the unique rule on the title attribute fails, the max rule will
                not be checked. Rules will be validated in the order they are assigned.

                A Note On Nested Attributes
                If your HTTP request contains "nested" parameters, you may specify them in your
                validation rules using "dot" syntax:

                <!--
                    $request->validate([
                        'title' => 'required|unique:posts|max:255',
                        'author.name' => 'required',
                        'author.description' => 'required',
                    ]);
                -->
            </writingTheValidationLogic>
            <displayingTheValidationErrors>
                So, what if the incoming request parameters do not pass the given validation rules? As mentioned
                previously, Laravel will automatically redirect the user back to their previous location.     In
                addition, all of the validation errors will automatically be flashed to the session.      Again,
                notice that we did not have to explicitly bind the error messages to the view in our GET  route.
                This is because Laravel will check for errors in the session data, and automatically bind   them
                to the view if they are available. The $errors variable will be an instance                   of
                Illuminate\Support\MessageBag. For more information on working with this object, check out   its
                documentation.

                <NOTE>
                    The $errors variable is bound to the view by the Illuminate\View\Middleware\ShareErrorsFromSession
                    middleware, which is provided by the web middleware group. When this middleware is applied      an
                    $errors variable will always be available in your views, allowing you to conveniently assume   the
                    $errors variable is always defined and can be safely used.
                </NOTE>

                So, in our example, the user will be redirected to our controller's create method when validation
                fails, allowing us to display the error messages in the view:

                <!--
                    // /resources/views/post/create.blade.php

                    <h1>Create Post</h1>

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                   // Create Post Form
                -->

                The @error Directive You may also use the @error Blade directive to quickly check if
                validation error messages exist for a given attribute. Within an @error   directive,
                you may echo the $message variable to display the error message:

                <!--
                    // /resources/views/post/create.blade.php

                    <label for="title">Post Title</label>

                    <input id="title" type="text" class="@error('title') is-invalid @enderror">

                    @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                -->
            </displayingTheValidationErrors>
            <aNoteOnOptionalFields>
                By default, Laravel includes the TrimStrings and ConvertEmptyStringsToNull middleware in
                your application's global middleware stack. These middleware are listed in the stack  by
                the App\Http\Kernel class. Because of this, you will often need to mark your  "optional"
                request fields as nullable if you do not want the vaildator to consider null values   as
                invalid. For example:

                <!--
                    $request->validate([
                        'title' => 'required|unique:posts|max:255',
                        'body' => 'required',
                        'publish_at' => 'nullable|date',
                    ]);
                -->

                AJAX Requests & Validation

                In this example, we used a traditional form to send data to the application. However, many applications
                use AJAX request. When using the validate method during an AJAX request, Laravel will not generate    a
                redirect response. Instead, Laravel generates a JSON response containing all of teh validation  errors.
                This JSON response will be sent with a 422 HTTP status code.
            </aNoteOnOptionalFields>
        </validationQuickstart>
        <formRequestValidation>
            <creatingFormRequests>
                For more complex validation scenarios, you may wish to create a "form request". Form requests are custom
                request classes that contain validation logic. To create a form request class, use the      make:request
                Artisan CLI command:

                <!--
                    php artisan make:request StoreBlogPost
                -->

                The generated class will be placed in the app/Http/Requests directory. If this directory does not exist,
                it will be created when you run the make:request command. Let's add a few validation rules to the  rules
                method:

                <!--
                     return [
                        'title' => 'required|unique:posts|max:255',
                        'body' => 'required',
                    ];
                -->

                <NOTE>
                    You may type-hint any dependencies you need within the rules method's signature. They           will
                    automatically be resolved via the Laravel service container.
                </NOTE>

                So, how are the validation rules evaluated? All you need to do is type-hint the request on your
                controller method. The incoming form request is validated before the controller method       is
                called, meaning you do not need to clutter your controller with any validation logic:

                <!--
                    /**
                     * Store the incoming blog post.
                     *
                     * @param  StoreBlogPost  $request
                     * @return Response
                     */
                    public function store(StoreBlogPost $request)
                    {
                        // The incoming request is valid...

                        // Retrieve the validated input data...
                        $validated = $request->validated();
                    }
                -->

                Adding After Hooks To Form Requests

                If you would like to add an after hook to a form request, you may use the withValidator method. This
                method receives the fully constructed validator, allowing you to call any of its methods before  the
                validation rules are actually evaluated:

                <!--
                    /**
                     * Configure the validator instance.
                     *
                     * @param  \Illuminate\Validation\Validator  $validator
                     * @return void
                     */
                    public function withValidator($validator)
                    {
                        $validator->after(function ($validator) {
                            if ($this->somethingElseIsInvalid()) {
                                $validator->errors()->add('field', 'Something is wrong with this field!');
                            }
                        });
                    }
                -->
            </creatingFormRequests>
            <authorizingFormRequests>
                The form request class also contains an authorize method. Within this method, you may check if the
                authenticated user actually has the authority to update a given resource. For example, you     may
                determine if a user actually owns a blog comment they are attempting to update:

                <!--
                    /**
                     * Determine if the user is authorized to make this request.
                     *
                     * @return bool
                     */
                    public function authorize()
                    {
                        $comment = Comment::find($this->route('comment'));

                        return $comment && $this->user()->can('update', $comment);
                    }
                -->

                Since all form requests extend the base Laravel request class, we may use the user method to access  the
                currently authenticated user. Also note the call to the route method in the exa,ple above. This   method
                grants you access to the URI parameters defined on the route being called, such as the         {comment}
                parameter. If teh authorize method returns false, a HTTP response with a 403 status code            will
                automatically be returned and your controller method wil not execute. If you plan to have  authorization
                logic in another part of your application, return true from the authorize method:

                <!--
                    /**
                     * Determine if the user is authorized to make this request.
                     *
                     * @return bool
                     */
                    public function authorize()
                    {
                        return true;
                    }
                -->

                <NOTE>
                    You may type-hint any dependencies you need within the authorize method's signature. They       will
                    automatically be resolved via the Laravel service container.
                </NOTE>
            </authorizingFormRequests>
            <customizingTheErrorMessages>
                You may customize the error messages used by the form request by overriding the messages method.    This
                method should return an array of attribute / rule pairs and their corresponding error messages:

                <!--
                    /**
                     * Get the error messages for the defined validation rules.
                     *
                     * @return array
                     */
                    public function messages()
                    {
                        return [
                            'title.required' => 'A title is required',
                            'body.required'  => 'A message is required',
                        ];
                    }
                -->

            </customizingTheErrorMessages>
            <customizingTheValidationAttributes>
                If you would like the :attribute portion of your validation message to be replaced with a         custom
                attribute name, you may specify the custom names by overriding the attributes method. This method should
                return an array of attribute / name pairs:

                <!--
                    /**
                     * Get custom attributes for validator errors.
                     *
                     * @return array
                     */
                    public function attributes()
                    {
                        return [
                            'email' => 'email address',
                        ];
                    }
                -->
            </customizingTheValidationAttributes>
            <prepareInputForValidation>
                If you need to sanitize any data from the request before you apply your validation rules, you can use
                the prepareForValidation method:

                <!--
                    use Illuminate\Support\Str;

                    /**
                     * Prepare the data for validation.
                     *
                     * @return void
                     */
                    protected function prepareForValidation()
                    {
                        $this->merge([
                            'slug' => Str::slug($this->slug),
                        ]);
                    }
                -->

            </prepareInputForValidation>
        </formRequestValidation>
        <manuallyCreatingValidators>
            If you do not want to use the validate method on the request, you may create a validator instance manually
            using the Validator facade. The make method on the facade generates a new validator instance:

            <!--
                namespace App\Http\Controllers;

                use App\Http\Controllers\Controller;
                use Illuminate\Http\Request;
                use Illuminate\Support\Facades\Validator;

                class PostController extends Controller
                {
                    /**
                     * Store a new blog post.
                     *
                     * @param  Request  $request
                     * @return Response
                     */
                    public function store(Request $request)
                    {
                        $validator = Validator::make($request->all(), [
                            'title' => 'required|unique:posts|max:255',
                            'body' => 'required',
                        ]);

                        if ($validator->fails()) {
                            return redirect('post/create')
                                        ->withErrors($validator)
                                        ->withInput();
                        }

                        // Store the blog post...
                    }
                }

            -->

            The first argument passed to the make method is the data under validation. The second argument is the
            validation rules that should be applied to the data. After checking if the request validation failed,
            you may use the withErrors method to flash the error messages to the session. When using this method,
            the $errors variable will automatically be shared with your views after redirection, allowing you  to
            easily display them back to the user. The withErrors method accepts a validator, a MessageBag, or   a
            PHP array.

            <automaticRedirection>
                If you would like to create a validator instance manually but still take advantage of the automatic
                redirection offered by the request's validate method, you may call the validate method on        an
                existing validator instance. If validation fails, the user will automatically be redirected or,  in
                the case of an AJAX request, a JSON response will be returned:

                <!--
                    Validator::make($request->all(), [
                        'title' => 'required|unique:posts|max:255',
                    ])->validate();
                -->
            </automaticRedirection>
            <namedErrorBags>
                If you have multiple forms on a single page, you may wish to name the MessageBag of errors, allowing you
                to retrieve the error messages for a specific form. Pass a name as teh second argument to withErrors:
                <!--
                    return redirect('register')->withErrors($validator, 'login');
                -->

                You may then access the named MessageBag instance from the $errors variable:

                <!--
                    {{  $errors->login->first('email')  }}
                -->

            </namedErrorBags>
            <afterValidationHook>
                The validator also allows you to attach callbacks to be run after validation is completed. This  allows
                you to easily perform further validation and even add more error messages to the message collection. To
                get started, use the after method on a validator instance:

                <!--
                    $validator = Validator::make(...);

                    $validator->after(function ($validator) {
                        if ($this->somethingElseIsInvalid()) {
                            $validator->errors()->add('field', 'Something is wrong with this field!');
                        }
                    });

                    if ($validator->fails()) {
                        //
                    }
                -->

            </afterValidationHook>
        </manuallyCreatingValidators>
        <workingWithErrorMessages>
            After calling the errors method on a Validator instance, you will receive an Illuminate\Support\MessageBag
            instance, which has a variety of convenient methods for working with error messages. The $errors  variable
            that is automatically made available to all views is also an instance of the MessageBag class.

            Retrieving The First Error Message For A Field
            To retrieve the first error message for a given field, use the first method:

            <!--
                $errors = $validator->errors();

                echo $errors->first('email');
            -->

            Retrieving All Error Messages For A Field
            If you need to retrieve an array of all the messages for a given field, use the get method:

            <!--
                foreach( $errors->get('email') as $msg) {
                    //
                }
            -->

            If you are validating an array form field, you may retrieve all of the messages for each of the array
            elements using the * character:

            <!--
                foreach($errors->get('attachments.*') as $msg) {
                    //
                }
            -->

            To retrieve an array of all messages for all fields, use the all method:

            <!--
                foreach ($errors->all() as $message) {
                    //
                }
            -->

            The has method may be used to determine if any error messages exist for a given field:

            <!--
                if ($errors->has('email')) {
                    //
                }
            -->

            <customErrorMessages>
                If needed, you may use custom error messages for validation instead of the defaults. There are several
                ways to specify custom messages. First, you may pass the custom messages as the argument to        the
                Validator::make method:

                <!--
                    $messages = [
                        'required' => 'The :attribute field is required.',
                    ];

                    $validator = Validator::make($input, $rules, $messages);
                -->

                In this example, the :attribute placeholder will be replaced by the actual name of the field under
                validation. You may also utilize other placeholders in validation messages. For example:

                <!--
                    $messages = [
                        'same'    => 'The :attribute and :other must match.',
                        'size'    => 'The :attribute must be exactly :size.',
                        'between' => 'The :attribute value :input is not between :min - :max.',
                        'in'      => 'The :attribute must be one of the following types: :values',
                    ];
                -->

                Specifying A Custom Message For A Given Attribute
                Sometimes you may wish to specify a custom error message only for a specific field. You may do so using
                "dot" notation. Specify the attribute's name first, followed by the rule:

                <!--
                    $messages = [
                        'email.required' => 'We need to fuck your mother',
                    ];
                -->

                Specifying Custom Messages In Language Files
                In most cases, you will probably specify your custom messages in a language file instead of passing them directly to the Validator. To do so, add your messages to custom array in resources/lang/xx/validation.php language file.

                <!--
                    'custom' => [
                        'email' => [
                            'required' => 'We need to know your e-mail address!',
                        ],
                    ],
                -->

                Specifying Custom Attributes In Language Files
                If you would like the :attribute portion of your validation message to be replaced  with
                a custom attribute name, you may specify the custom name in the attributes array of your
                resources/lang/xx/validation.php language file.

                <!--
                    'attributes' => [
                        'email' => 'email address',
                    ],
                -->

                Specifying Custom Values In Language Files
                Sometimes you may need the :value portion of your validation message to be replaced with a  custom
                representation of the value. For example, consider the following rule that specifies that a credit
                card number is required if the payment_type has a value of cc:

                <!--
                    $request->validate([
                        'credit_card_number' => 'required_if:payment_type,cc'
                    ]);
                -->

                If this validation rule fails, it will produce the following error message:

                <!--
                    The credit card number field is required when payment type is cc.
                -->

                Instead of displaying cc as the payment type value, you may specify a custom value representation in our
                validation language file by defining a values array:

                <!--
                    'values' => [
                        'payment_type' => [
                            'cc' => 'credit card'
                        ],
                    ],
                -->

                Now if the validation rule fails it will produce the following message:

                <!--
                    The credit card number field is required when payment type is credit card.
                -->

            </customErrorMessages>
        </workingWithErrorMessages>
        <availableValidationRules>
            <Accepted>
                The field under validation must be yes, on, 1, or true. This is useful for validating "Terms of Service"
                acceptance.
            </Accepted>
            <ActiveURL>
                The field under validation must have a valid A or AAAA record according to the dns_get_record PHP
                function. The hostname of the provided URL is extracted using the parse_url PHP function   before
                being passed to dns_get_record.
            </ActiveURL>
            <After_Date>
                after:date
                The field under validation must be a value after a given date. The dates will be passed into the
                strtotime PHP function:

                <!--
                    'start_date' => required|date|after:tomorrow',
                -->

                Instead of passing a date string to be evaluated by strtotime, you may specify another field to
                compare against the date:

                <!--
                    'finish_date' => 'required|date|after:start_date',
                -->
            </After_Date>
            <AfterOrEqual_Date>
                after_or_equal:date
                The field under validation must be a value after or equal to the given date. For more information,
                see the after rule
            </AfterOrEqual_Date>
            <Alpha>
                The field under validation must be entirely alphabetic characters.
            </Alpha>
            <AlphaDash>
                The field under validation may have alpha_numeric characters, as well as dashes and underscores.
            </AlphaDash>
            <AlphaNumeric>
                The field under validation must be entirely alpha-numeric characters.
            </AlphaNumeric>
            <Array>
                The field under validation must be a PHP array.
            </Array>
            <Bail>
                Stop running validation rules after the first validation failure.
            </Bail>
            <Before_Date>
                before:date
                The field under validation must be a value preceding the given date. The dates will be passed into
                the PHP strtotime function. In addition, like the after rule, the name of another field      under
                validation may be supplied as the value of date.
            </Before_Date>
            <BeforeOrEqual_Dat>
                before_or_equal:date
                The field under validation must be a value preceding or equal to the given date. The dates will  be
                passed into the PHP strtotime function. In addition, like the after rule, the name of another field
                under validation may be supplied as the value of date.
            </BeforeOrEqual_Dat>
            <Between>
                between:min,max
                The field under validation must have a size between the given min and max. Strings, numeric, arrays, and
                files are evaluated in the same fashion as the size rule.
            </Between>
            <Boolean>
                The field under validation must be able to be cast as a boolean. Accepted input are true, false, 1, 0,
                "1", and "0"
            </Boolean>
            <Confirmed>
                The field under validation must have a matching field of foo_confirmation. For example, if the field
                under validation is password, a matching password_confirmation field must be present in the input.
            </Confirmed>
            <Date>
                The field under validation must be a valid, non-relative date according to the strtotime PHP function.
            </Date>
            <DateEquals>
                date_equals:date
                The field under validation must be equal to the given date. The dates will be passed into
                the PHP strtotime function
            </DateEquals>
            <DateFormat>
                date_format:format
                The field under validation must match the given format. You should use either  date or date_format
                when validating a field, not both. This vaildaiton rule supports all formats supported by    PHP's
                DateTime class.
            </DateFormat>
            <Different>
                different:field
                The field under validation must have a different value than field.
            </Different>
            <Digits>
                digits:value
                The field under validation must be numeric and must have an exact length of value.
            </Digits>
            <DigitsBetween>
                digits_between:min,max
                The field under validation must be numeric and must have a length between the given min and max.
            </DigitsBetween>
            <Dimensions_ImageFiles>
                dimensions
                The field under validation must be an image meeting the dimension constraints as specified by
                the rule's parameters:

                <!--
                    'avatar' => 'dimensions:min_width=100,min_height=200'
                -->

                Available constraints are: min_width, max_width, min_height, max_height, width, height, ratio.
                A ratio constraint should be represented as width divided by height. This can be    specified
                either by a statement like 3/2 or a float like 1.5

                <!--
                    'avatar' => 'dimensions:ratio=3/2'
                -->
                Since this rule requires several arguments, you may use the Rule::dimensions method to fluently
                construct the rule:

                <!--
                    use Illuminate\Validation\Rule;

                    Validator::make($data, [
                        'avatar' => [
                            'required',
                            Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3 / 2),
                        ],
                    ])
                -->
            </Dimensions_ImageFiles>
            <Distinct>
                When working with arrays, the field under validation must not have any duplicatie values.

                <!--
                    'foo.*.id' => 'distinct'
                -->
            </Distinct>
            <E-Mail>
                The field under validation must be formatted as an e-mail address. Under the hood, this validation rule
                makes use of the egulias/email-validator package for validating the email address. By default       the
                RFCValidation validator is applied, but you can apply other validation styles as well:

                <!--
                    'email' => 'email:rfc,dns'
                -->

                The example above will apply the RFCValidation and DNSCheckValidation validations. Here's a full list of
                validation styles you can apply:
                    1- rfc: RFCValidation
                    2- strict: NoRFCWarningsValidation
                    3- dna: DNSCheckValidation
                    4- spoof: SpoofCheckValidation
                    5- filter: FilterEmailValidation

                The filter validator, which uses PHP's filter_var function under the hood, ships with Laravel and is
                Laravel pre-5.8 behavior. The dns and spoof validators require the PHP intl extension.
            </E-Mail>
            <EndsWith>
                eds_with:foo,bar,...
                The field under validation must end with one of the given values.
            </EndsWith>
            <ExcludeIf>
                exclude_if:anotherfield,value
                The field under validation will be excluded from the request data returned by the validate and validated
                methods if the anotherfield field is equal to value.
            </ExcludeIf>
            <ExcludeUnless>
                exclude_unless:anotherfield,value
                The field under validation will be excluded from the request data returned by the validate and validated
                methods unless anotherfield's field is equal to value.
            </ExcludeUnless>
            <Exists_Database>
                exists:table,column
                The field under validation must exist on a given database table.
                Basic Usage Of Exists Rule

                <!--
                    'state' => 'exists:states'
                -->

                If the column option is not specified, the field name will be used

                Specifying A Custom Column Name

                <!--
                    'state' => 'exists:states,abbreviation'
                -->

                Occasionally, you may need to specify a specific database connection to be used for the exists query.
                You can accomplish this by prepending the connection name to the table name using "dot" syntax:

                <!--
                    'email' => 'exists:connection.staff,email'
                -->

                Instead of specifying the table name directly, you may specify the Eloquent model which should be
                used to determine the table name:

                <!--
                    'user_id' => 'exists:App\User,id'
                -->

                If you would like to customize the query executed by the validation rule, you may use the Rule class to
                fluently define the rule. In this example, we'll also specify the validation rules as an array  instead
                of using the | character to delimit them:

                <!--
                    use Illuminate\Validation\Rule;

                    Validator::make($data, [
                        'email' => [
                            'required',
                            Rule::exists('staff')->where(function ($query) {
                                $query->where('account_id', 1);
                            }),
                        ],
                    ]);
                -->
            </Exists_Database>
            <File>
                The field under validation must be a successfully uploaded file.
            </File>
            <Filled>
                The field under validation must not be empty when it is present.
            </Filled>
            <GreaterThan>
                gt:field
                The field under validation must be greater than the given field. The two fields must be of the same
                type. Strings, numeric, array, and files are evaluated using the same conventions as the size rule.
            </GreaterThan>
            <GreaterThanOrEqual>
                gte:field
                The field under validation must be greater than or equal to the given field. The two fields must be of
                the same type. Strings, numeric, array, and files are evaluated using the same conventions as the size
                rule.
            </GreaterThanOrEqual>
            <Image_File>
                image
                The field under validation must be an image (jpeg,png,bmp,gif,svg,or webp)
            </Image_File>
            <In>
                in:foo,bar,...
                The field under validation must be included in the given list of values. Since this rule often requires
                you to implode an array, the Rule::in method may be used to fluently construct the rule

                <!--
                    use Illuminate\Validation\Rule;

                    Validator::make($data, [
                        'zones' => [
                            'required',
                            Rule::in(['first-zone', 'second-zone']),
                        ],
                    ]);
                -->
            </In>
            <InArray>
                in_array:anotherfield.*
                The field under validation must exist in anotherfield's values.
            </InArray>
            <Integer>
                The field under validation must an integer.
                <NOTE>
                    This validation rule does not verify that the input is of the "integer" variable type, only that the
                    input is a string or numeric value that contains an integer.
                </NOTE>
            </Integer>
            <IPAddress>
                ip
                The field under validation must be an IP address.

                ipv4
                The field under validation must an IPv4 address.

                ipv6
                The field under validation must an IPv6 address.
            </IPAddress>
            <JSON>
                The field under validation must be a valid JSON string.
            </JSON>
            <LessThan>
                lt:field
                The field under validation must be less than the given field. The two fields must be of the same type.
            </LessThan>
            <LessThanOrEqual>
                lte:field
                The field under validation must be less than or equal to the given field. The two fields must be of the
                same type
            </LessThanOrEqual>
            <Max>
                max:value
                The field under validation must be less than or equal to a maximum value.
            </Max>
            <MIMETypes>
                mimetypes:text/plain,...
                The field under validation must match one of the given MIME types:

                <!--
                    'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'
                -->

                To determine the MIME type of the uploaded file, the file's contents will be read and the framework
                will attempt to guess the MIME type, which may be different from the client provided MIME type.
            </MIMETypes>
            <MIMETypeByFileExtension>
                The field under validation must have a MIME type corresponding to one of the listed extensions.

                Basic Usage Of MIME Rule

                <!--
                    'photo' => 'mimes:jpeg,bmp,png'
                -->

                Even though you only need to specify the extensions, this rule actually validates against the MIME
                type of the file by reading the file's contents and guessing its MIME type. A full listing of MIME
                types and their corresponding extensions may be found at the following location:
                    https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
            </MIMETypeByFileExtension>
            <Min>
                The field under validation must have minimum value.
            </Min>
            <NotIn>
                not_in:foo,bar,..
                The field under validation must not be included in the given list of values. The Rule::notIn method
                may be used to fluently construct the rule

                <!--
                    use Illuminate\Validation\Rule;

                    Validator::makeValidator::make($data, [
                        'toppings' => [
                            'required',
                            Rule::notIn(['sprinkles', 'cherries']),
                        ],
                    ]);
                -->
            </NotIn>
            <NotRegex>
                not_regex:pattern
                The field under validation must not match the given regular expression.

                Internally, this rule uses the PHP preg_match function. The pattern specified should obey the same
                formatting required by preg_match and thus also include valid delimiters. For example:

                <!--
                    'email' => 'not_regex:/^.+$/i'
                -->

                <NOTE>
                    When using the regex /not_regex patterns, it may be necessary to specify rules in an array instead
                    of using pipe delimiters, especially if the regular expression contains a pipe character.
                </NOTE>
            </NotRegex>
            <Nullable>
                The field under validation may be null. This is particularly useful when validating primitive such as
                strings and integers that can contain null values.
            </Nullable>
            <Numeric>
                The field under validation must numeric
            </Numeric>
            <Password>
                The field under validation must match the authenticated user's password. You may specify an
                authentication guard using the rule's first parameter:

                <!--
                    'password' => 'password:api'
                -->
            </Password>
            <Present>
                The field under validation must be present in the input data but can be empty.
            </Present>
            <RegularExpression>
                regex:pattern
                The field under validation must match the given regular expression.
                see not_regex
            </RegularExpression>
            <Required>
                The field under validation must be present in the input data nad not empty. A field is considered
                "empty" if one of the following conditions are true:
                    1- The value is null
                    2- The value is an empty string.
                    3- The value is an empty array or empty Countable object.
                    4- The value is uploaded file with no path.
            </Required>
            <RequiredIf>
                required_if:anotherfield,value,...
                The field under validation must be present and not empty if the anotherfield field is equal to  any
                value. If you would like to construct a more complex condition for the required_if rule, you    may
                use the Rule::requiredIf method. This methods accepts a boolean or a Closure. When passed a Closure
                the Closure should return true or false to indicate if the field under validation is required:

                <!--
                    use Illuminate\Validation\Rule;

                    Validator::make($request->all()), [
                        'role_id' => Rule::requiredIf($request->user()->is_admin);
                    ]);

                    Validator::make($request->all(), [
                        'role_id' => Rule::requiredIf(function () use ($request) {
                           return $request->user()->is_admin;
                        }),
                    ]);
                -->
            </RequiredIf>
            <RequiredUnless>
                required_unless:anotherfield,value,...
                The field under validation must be present and not empty unless the anotherfield field
                is equal to any value.
            </RequiredUnless>
            <RequiredWith>
                required_with:foo,bar,..
                The field under validation must be present and empty only if any of
                the other specified fields are present.
            </RequiredWith>
            <RequiredWithAll>
                required_with_all:foo,bar,...
                The field under validation must be present and not empty only if all of the other specified
                fields are present.
            </RequiredWithAll>
            <RequiredWithout>
                required_without:foo,bar,...
                The field under validation must be present and not empty only when any of the other specified
                fields are not present.
            </RequiredWithout>
            <RequiredWithoutAll>
                The field under validation must present and not empty only when all of the other specified
                fields are not present
            </RequiredWithoutAll>
            <Same>
                same:field
                The given field must match the field under validation
            </Same>
            <Size>
                size:value
                The field under validation must have a size matching the given value. For string data, value corresponds
                to the number of characters. For numeric data, value corresponds to a given integer value (the attribute
                must also have the numeric or integer rule). For an array, size corresponds to the count of the   array.
                For files, size corresponds to the file size in kilobytes. Let's look at some examples:

                <!--
                    // Validate that a string is exactly 12 characters long...
                    'title' => 'size:12'

                    // Validate that a provided integer equals 12
                    'seats' => 'integer|size:12'

                    //Validate that an array has exactly 5 elements...
                    'tags' => 'array|size:5'

                    // Validate that an uploaded file is exactly 512 kilobytes...
                    'image' => 'file|size:512'
                -->
            </Size>
            <StartsWith>
                start_with:foo,bar,...
                The field under validation must start with one of the given values.
            </StartsWith>
            <String>
                string
                The field under validation must be a string. If you would like to allow the field to also be null,
                you should assign the nullable rule to the field.
            </String>
            <Timezone>
                timezone
                The field under validation must a valid timezone identifier according to the timezone_identifiers_list
                PHP function.
            </Timezone>
            <Unique_Database>
                unique:table,column,except,idColumn
                The field under validation must no exist within the given database table

                Specifying A Custom Table / Column Name:
                Instead of specifying the table name directly, you may specify the Eloquent model which should be
                used to determine the table name:

                <!--
                    'email' => 'unique:App\User,email_address'
                -->

                The column option may be used to specify the field's corresponding database column. If the
                column option is not specified, the field name will be used.

                <!--
                    'email' => 'unique:users,email_address'
                -->

                Custom Database Connection
                Occasionally, you may need to set a custom connection for database queries made by the Validator.
                As seen above, setting unique:users as a validation rule will use the default database connection
                to query the database. To override this, specify the connection and the table name using    "dot"
                syntax:

                <!--
                    'email' => 'unique:connection.users,email_address'
                -->

                Forcing A Unique Rule To Ignore A Given ID:
                Sometimes, you may wish to ignore a given ID during the unique check. For example, consider an
                "update profile" screen that includes the user's name, e-mail address, and location. You  will
                probably want to verify that the e-mail address is unique. However, if the user only   changes
                the name field and not the e-mail field, you do not want a validation error to be       thrown
                because the user is already the owner of the e-mail address.

                To instruct the validator to ignore the user's id, we'll use the Rule class to fluently  define
                the rule. In this example, we'll also specify the validation rules as an array instead of using
                the | character to delimit the rules:

                <!--
                    use Illuminate\Validation\Rule;

                    Validator::make($data, [
                        'email' => [
                            'required',
                            Rule::unique('users')->ignore($user->id),
                        ],
                    ]);
                -->

                <NOTE>
                    You should never pass any user controlled request input into the ignore method. Instead,  you
                    should only pass a system generated unique ID such as an auto-incrementing ID or UUID from an
                    Eloquent model instance. Other wise, your application will be vulnerable to an SQL  injection
                    attack.
                </NOTE>

                Instead of passing the model key's value to the ignore method, you may pass the entire model
                instance. Laravel will automatically extract the key from the model:

                <!--
                    Rule::unique('users')->ignore($user)
                -->

                If your table uses a primary key column name other than id, you may specify the name of the
                column when calling the ignore method:

                <!--
                    Rule::unique('users')->ignore($user->id, 'user_id')
                -->

                By default, the unique rule will check the uniqueness of the column matching the name of    the
                attribute being validated. However, you may pass a different column name as the second argument
                to the unique method:

                <!--
                    Rule:unique('users', 'email_address')->ignore($user->id),
                -->

                Adding Additional Where Clauses:
                You may also specify additional query constraints by customizing the query using the where
                method. For example, let's add a constraint that verifies the account_id is 1:

                <!--
                    'email' => Rule::unique('users')->where(function ($query) {
                        return $query->where('account_id', 1);
                    })
                -->

            </Unique_Database>
            <URL>
                url
                The field under validation must be a valid URL.
            </URL>
            <UUID>
                uuid
                The field under validation must be a valid RFC 4122 (version 1, 3, 4, or 5) universally unique
                identifier (UUID).
            </UUID>
        </availableValidationRules>
        <conditionallyAddingRules>
            Validating When Present
            In some situations, you may wish to run validation checks against a field only if
            that field is present iin the input array. To quickly accomplish this, add    the
            sometimes rule to your rule list:

            <!--
                $v = Validator::make($data, [
                    'email' => 'sometimes|required|email',
                ]);
            -->

            In the example above, the email field will onnly be validated if it is present
            in the $data array.

            <NOTE>
                If you are attempting to validate a field that should always be present but
                may be empty, check out this note on option fields
                https://laravel.com/docs/6.x/validation#a-note-on-optional-fields
            </NOTE>

            Complex Conditional Validation
            Sometimes you may wish to add validation rules based on more complex conditional logic. For example,
            you may wish to require a given field only if another field has a greater value than 100 Or, you may
            need two fields to have a given vlaue only when another field is present. Adding these    validation
            rules doesn't have to be a pain. First, create a Validator instance with your static rules      that
            never change:

            <!--
                $v = Validator::make($data, [
                    'email' => 'required|email',
                    'games' => 'required|numeric',
                ]);
            -->

            Let's assume our web application is for game collectors. If a game collector registers with our application
            and they own more than 100 games, we want them to explain why they own so many games. For example,  perhaps
            they run a game resale shop, or maybe they just enjoy collecting. To conditionally add this requirement, we
            can use the sometimes method on the Validator instance.

            <!--
                $v->sometimes('reason', 'required|max:500', function ($input) {
                    return $input->games >= 100;
                });
            -->

            The first argument passed to the sometimes method is the name of the field we are conditionally validating.
            The second argument is the rules we want to add. If the Closure passed as the third argument returns  true,
            the rules will be added. This method makes it a breeze to build complex conditional validations. You    may
            even add conditional validations for several fields at once:

            <!--
                $v->sometimes(['reason', 'cost'], 'required', function ($input) {
                    return $input->games >= 100;
                });
            -->

            <NOTE>
                The $input parameter passed to your Closure will be an instance of Illuminate\Support\Fluent
                and may be used to access your input and files.
            </NOTE>

        </conditionallyAddingRules>
        <validatingArrays>
            Validating array based form input fields doesn't have to be a pain. You may use "dot notation" to validate
            attributes within an array. For  example, if the incoming HTTP request contains a photos[profile]    field
            you may validate it like so:

            <!--
                $validator = Validator::make($request->all(), [
                    'photos.profile' => 'required|image',
                ]);
            -->

            You may also validate each element of an array. For example, to validate that each e-mail in a given array
            input field is unique, you may do the following:

            <!--
                $validator = Validator::make($request->all(), [
                    'person.*.email' => 'email|unique:users',
                    'person.*.first_name' => 'required_with:person.*.last_name',
                ]);
            -->

            Likewise, you may use the * character when specifying your validation messages in your language files,
            making it a breeze to use a single validation message for array based fields:

            <!--
                'custom' => [
                    'person.*.email' => [
                        'unique' => 'Each person must have a unique e-mail address',
                    ]
                ],
            -->

        </validatingArrays>
        <customValidationRules>
            <usingRuleObjects>
                Laravel provides a variety of helpful validation rules; however, you may wish to specify some of
                your own. One method of registering custom validation rules is using rule objects. To generate a
                new rule object, you may use the make:rule Artisan command. Let's use this command to generate a
                rule that verifies a string is uppercase. Laravel will place the new rule in the app/Rules
                directory:

                <!--
                    php artisan make:rule Uppercase
                -->

                Once the rule has been created, we are ready to define its behavior. A rule object contains two methods:
                passes and message. The passes method receives the attribute value and name, and should return true   or
                false depending on whether the attribute value is valid or not. The message method should return    the
                validation error message that should be used when validation fails:

                <!--
                    namespace App\Rules;

                    use Illuminate\Contracts\Validation\Rule;

                    class Uppercase implements Rule
                    {
                        /**
                         * Determine if the validation rule passes.
                         *
                         * @param  string  $attribute
                         * @param  mixed  $value
                         * @return bool
                         */
                        public function passes($attribute, $value)
                        {
                            return strtoupper($value) === $value;
                        }

                        /**
                         * Get the validation error message.
                         *
                         * @return string
                         */
                        public function message()
                        {
                            return 'The :attribute must be uppercase.';
                        }
                    }
                -->

                You may call the trans helper from your message method if you would like to return an error
                message from your translation files:

                <!--
                    /**
                     * Get the validation error message.
                     *
                     * @return string
                     */
                    public function message()
                    {
                        return trans('validation.uppercase');
                    }
                -->

                Once the rule has been defined, you may attach it to a validator by passing an instance
                of the rule object with your other validation rules:

                <!--
                    use App\Rules\Uppercase;

                    $request->validate([
                        'name' => ['required', 'string', new Uppercase],
                    ]);
                -->
            </usingRuleObjects>
            <usingClosures>
                If you only need the functionality of a custom rule once throughout your application, you may use
                a Closure instead of a rule object. The Closure receives the attribute's name, attribute's
                value, and a $fail callback that should be called if validation fails:

                <!--
                    $v = Validator::make($request->all(), [
                        'title' => [
                            'required',
                            'max:255',
                            function ($attribute, $value, $fail) {
                                if ($value === 'foo') {
                                    $fail($attribute.' is invalid.');
                                }
                            ),
                        ],
                    ]);
                -->

            </usingClosures>
            <usingExtensions>
                Another method of registering custom validation rules is using the extend method on the
                Validator facade. Let's use this method within a service provider to register a  custom
                validation rule:

                <!--
                    namespace App\Providers;

                    use Illuminate\Support\ServiceProvider;
                    use Illuminate\Support\Facades\Validator;

                    class AppServiceProvider extends ServiceProvider
                    {
                        /**
                         * Register any application services.
                         *
                         * @return void
                         */
                        public function register()
                        {
                            //
                        }

                        /**
                         * Bootstrap any application services.
                         *
                         * @return void
                         */
                        public function boot()
                        {
                            Validator::extend('foo', function ($attribute, $value, $parameters, $validator) {
                                return $value == 'foo';
                            });
                        }
                    }
                -->

                The custom validator Closure receives four arguments: the name of the $attribute being validated,
                the $value of the attribute, an array of $parameters passed to the rule, and the        Validator
                instance. You may also pass a class and method to the extend method instead of a Closure:

                <!--
                    Validator::extend('foo', 'FooValidator@validate');
                -->

                Defining The Error Message
                You will also need to define an error message for you custom rule. You can do so either using an  inline
                custom message array or by adding an entry in the validation language file. This message should       be
                placed in the first level of the array, not within the custom array, which is only for attribute-specific
                error messages:

                <!--
                    "foo" => "Your input was invalid!",

                    "accepted" => "The :attribute must be accepted.",

                    // The rest of the validation error messages...
                -->

                When creating a custom validation rule, you may sometimes need to define custom placeholder replacements
                for error messages. You may do so by creating a custom Validator as described above then making a   call
                to the replacer method on the Validator facade. You may do this within the boot method of a      service
                provider:

                <!--
                    /**
                     * Bootstrap any application services.
                     *
                     * @return void
                     */
                    public function boot()
                    {
                        Validator::extend(...);

                        Validator::replacer('foo', function ($message, $attribute, $rule, $parameters) {
                            return str_replace(...);
                        });
                    }
                -->
            </usingExtensions>
            <implicitExtensions>
                By default, when an attribute being validated is not present or contains an empty string,  normal
                validation rules, including custom extensions, are not run. For example, the unique rule will not
                be run against an empty string:

                <!--
                    $rules = ['name' => 'unique:users,name'];

                    $input = ['name' => ''];

                    Validator::make($input, $rules)->passes(); // true
                -->

                For a rule to run even when an attribute is empty, the rule must imply that the attribute is
                required. To create such an "implicit" extension, use the Validator::extendImplicit() method:

                <!--
                    Validator::extendImplicit('foo', function ($attribute, $value, $parameters, $validator) {
                        return $value == 'foo';
                    });
                -->

                <NOTE>
                    An implicit extension only implies that the attribute is required.
                    Whether it actually invalidates a missing or empty attribute    is
                    up to you.
                </NOTE>

                Implicit Rule Objects
                If you would like a rule object to run when an attribute is empty, you should implement  the
                Illuminate\Contracts\Validation\ImplicitRule interface. This interface serves as a    marker
                interface for the validator therefore, it does not contain any methods you need to implement
            </implicitExtensions>
        </customValidationRules>
    </validation>
    <errorHandling>
        <introduction>
            When you start a new Laravel project, error and exception handling is already configured  for   you.
            The App\Exceptions\Handler class is where all exceptions triggered by your application  are   logged
            and then rendered back to the user. We'll dive deeper into this class throughout this documentation.
        </introduction>
        <configuration>
            The debug option in your config/app.php configuration file determines how much information about an
            error is actually displayed to the user. By default, this option is set to respect the value of the
            APP_DEBUG environment variable, whic is stored in your .env file. For local development, you should
            set the APP_DEBUG environment variable to true. In your production environment, this value   should
            always be false. If the value is set to true in production, you risk exposing             sensitive
            configuration values to your applications's end users.
        </configuration>
        <theExceptionHandler>
            <reportMethod>
                All exceptions are handled by the App\Exceptions\Handler class. This class contains two methods: report
                and render. We'll examine each of these methods in detail. The report method is used to log  exceptions
                or send them to an external service like Flare, Bugsnag or Sentry. By default, the report method passes
                the exception to the base class where the exception is logged. However, you are free to log   exception
                however you wish. For  example, if you need to report different types of exceptions in different   ways,
                you may use the PHP instanceof comparison operator:

                <!--
                    public function report(Exception $exception)
                    {
                        if ($exception instanceof CustomException) {
                            //
                        }

                        parent::report($exception);
                    }
                -->

                <NOTE>
                    Instead of making a lot of instanceof checks in your report method consider using
                    reportable exceptions.
                </NOTE>

                Global Log Context
                If available, Laravel automatically adds the current user's ID to every exception's log message  as
                contextual data. You may define your own global contextual data by overriding the context method of
                your application's App\Exceptions\Handler class. This information will be included in         every
                exception's log message written by your application:

                <!--
                    /**
                     * Get the default context variables for logging.
                     *
                     * @return array
                     */
                    protected function context()
                    {
                        return array_merge(parent::context(), [
                            'foo' => 'bar',
                        ]);
                    }
                -->

                The report Helper
                Sometimes you may need to report an exception but continue handling the current request. The    report
                helper function allows you to quickly report an exception using your exception handler's report method
                without rendering an error page:

                <!--
                    public function isValid($value)
                    {
                        try {
                            // Validate the value...
                        } catch (Exception $e) {
                            report($e);

                            return false;
                        }
                    }
                -->

                Ignoring Exceptions By Type
                The $dontReport property of the exception handler contains an array of exception types that will not  be
                logged. For example, exceptions resulting from 404 errors, as well as several other types of errors, are
                not written to your log files. You may add other exception types to this array as needed:

                <!--
                    /**
                     * A list of the exception types that should not be reported.
                     *
                     * @var array
                     */
                    protected $dontReport = [
                        \Illuminate\Auth\AuthenticationException::class,
                        \Illuminate\Auth\Access\AuthorizationException::class,
                        \Symfony\Component\HttpKernel\Exception\HttpException::class,
                        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
                        \Illuminate\Validation\ValidationException::class,
                    ];
                -->

            </reportMethod>
            <renderMethod>
                The render method is responsible for converting a given exception into an HTTP response that should be
                sent back to the browser. By default, the exception is passed to the base class which generates      a
                response for you. However, you are free to check the exception type or return your own custom reponse:

                <!--
                    /**
                     * Render an exception into an HTTP response.
                     *
                     * @param  \Illuminate\Http\Request  $request
                     * @param  \Exception  $exception
                     * @return \Illuminate\Http\Response
                     */
                    public function render($request, Exception $exception)
                    {
                        if ($exception instanceof CustomException) {
                            return response()->view('errors.custom', [], 500);
                        }

                        return parent::render($request, $exception);
                    }
                -->
            </renderMethod>
            <reportableAndRenderableExceptions>
                Instead of type-checking exceptions in the exception handler's report and render methods, you may define
                report and render methods directly on your custom exception. When these methods exist, they will      be
                called automatically by the frame work:

                <!--
                    namespace App\Exceptions;

                    use Exception;

                    class RenderException extends Exception
                    {
                        /**
                         * Report the exception.
                         *
                         * @return void
                         */
                        public function report()
                        {
                            //
                        }

                        /**
                         * Render the exception into an HTTP response.
                         *
                         * @param  \Illuminate\Http\Request  $request
                         * @return \Illuminate\Http\Response
                         */
                        public function render($request)
                        {
                            return response(...);
                        }
                    }
                -->

                <NOTE>
                    You may type-hint any required dependencies of the report method and they will automatically be
                    injected into the method by Laravel's service container
                </NOTE>

            </reportableAndRenderableExceptions>
        </theExceptionHandler>
        <HTTPExceptions>
            Some exceptions describe HTTP error codes from the serve. For example, this may be a "page not found" error
            (404), an "unauthorized error" (401) or even developer generated 500 error. In order to generate such     a
            response from anywhere in your application, you may use the abort helper:

            <!--
                abort(404);
            -->

            The abort helper will immediately raise an exception which will be rendered by the exception handler.
            Optionally, you may provide the response text:

            <!--
                abort(403, "unauthorized action.');
            -->
            <customHTTPErrorPages>
                Laravel makes it easy to display custom error pages for various HTTP status codes. For example, if    you
                wish to customize the error page for 404 HTTP status codes, create a  resource/views/errors/404.blade.php
                This file will be served on all 404 errors generated by your application. The views within this directory
                should be named to match the  HTTP status code they correspond to. The HttpException instance raised   by
                the abort function will be passed to the view as an $exception variable:

                <!--
                    <h2>{{  $exception->getMessage()  }} </h2>
                -->

                You may publish Laravel's error page templates using the vendor:publish Artisan command.
                Once the templates have been published, you may customize them to your liking:

                <!--
                    php artisan vendor:publish --tag=laravel-errors
                -->

            </customHTTPErrorPages>
        </HTTPExceptions>
    </errorHandling>
    <logging>
        <introduction>
            To help you learn more about what's happening within your application, Laravel provides robust  logging
            services that allow you to log messages to files, the system error log, and even to Slack to     notify
            your entire team. Under the hood, Laravel utilizes the Monolog library, which provides support for    a
            variety of powerful log handlers. Laravel makes it a cinch to configure these handlers, allowing you to
            mix and match them to customize your application's log handling.
        </introduction>
        <configuration>
            All of the configuration for your application's logging system is housed in the config/logging.php  file.
            This file allows you to configure your application's log channels, so be sure to review each of       the
            available channels and their options. We'll review a few common options below. By default, Laravel   will
            use the stack channel when logging messages. The stack channel is used to aggregate multiple log channels
            into a single channel. For more information on building stacks.

            Configuring The Channel Name
            By default, Monolog in instantiated with a "channel name" that matches the current environment, such as
            production or local. To change this value, add a name option to your channel's configuration:

            <!--
                'stack' => [
                    'driver' => 'stack',
                    'name' => 'channel-name',
                    'channels' => ['single', 'slack'],
                ],
            -->

            Available Channel Drivers

             ____________________________________________________________________________________
            |Name	    |    Description                                                         |
            |-----------|------------------------------------------------------------------------|
            |stack	    |    A wrapper to facilitate creating "multi-channel" channels           |
            |single	    |    A single file or path based logger channel (StreamHandler)          |
            |daily	    |    A RotatingFileHandler based Monolog driver which rotates daily      |
            |slack	    |    A SlackWebhookHandler based Monolog driver                          |
            |papertrail	|    A SyslogUdpHandler based Monolog driver                             |
            |syslog	    |    A SyslogHandler based Monolog driver                                |
            |errorlog	|    A ErrorLogHandler based Monolog driver                              |
            |monolog	|    A Monolog factory driver that may use any supported Monolog handler |
            |custom	    |    A driver that calls a specified factory to create a channel         |
            |____________________________________________________________________________________|

            <NOTE>
                Check out the documentation on advanced channel customization to learn more about
                the monolog and custom drivers.
            </NOTE>

            Configuring The Single and Daily Channels
            The single and daily channels have three optional configuration options: bubble, permission, and locking.

            |Name	    | Description	                                                               | Default |
            |-----------|------------------------------------------------------------------------------|---------|
            |bubble	    | Indicates if messages should bubble up to other channels after being handled | true    |
            |permission | The log file's permissions	                                               | 0644    |
            |locking	| Attempt to lock the log file before writing to it                            | false   |

            Configuring The Papertrail Channel
            The papertrail channel requires the url and port configuration options. You can obtain these values from
            Papertrail.
            https://help.papertrailapp.com/kb/configuration/configuring-centralized-logging-from-php-apps/#send-events-from-php-app

            Configuring The Slack Channel
            The slack channel requires a url configuration option. This URL should match a URL for incoming     webhook
            that you have configured for you Slack team. By default, Slack will only receive logs at the critical level
            and above; however, you can adjust this in your logging configuration file.

            <buildingLogStacks>
                As previously mentioned, the stack driver allows you to combine multiple channels into a single  log
                channel. To illustrate how to use log stacks, let's take a look at an example configuration that you
                might see in a production application:

                <!--
                    'channels' => [
                        'stack' => [
                            'driver' => 'stack',
                            'channels' => ['syslog', 'slack'],
                        ],

                        'syslog' => [
                            'driver' => 'syslog',
                            'level' => 'debug',
                        ],

                        'slack' => [
                            'driver' => 'slack',
                            'url' => env('LOG_SLACK_WEBHOOK_URL'),
                            'username' => 'Laravel Log',
                            'emoji' => ':boom:',
                            'level' => 'critical',
                        ],
                    ],
                -->

                Let's dissect this configuration. First, notice our stack channel aggregates two other channels via its
                channels option: syslog and slack. So, when logging messages, both of these channels will have      the
                opportunity to log the message.

                Log Levels
                Take not of the level configuration option present on the syslog and slack channel configurations in the
                example above. This option determines the minimum "level" a message must be in order to be logged by the
                channel. Monolog, which powers Laravel's logging services, offers all of the log levels defined in   the
                RFC 5424 specification: emergency, alert, critical, error, warning, notice, info, and debug.

                So, imagine we log a message using the debug method:

                <!--
                    Log::debug('An informational message.');
                -->

                Given our configuration, the syslog channel will write the message to the system log; however, since the
                error message is not critical or above, it will not be set to Slack. However, if we log an     emergency
                message, it will be sent to both the system log and Slack since the emergency level is above our minimum
                level threshold for both channels:

                <!--
                    Log::emergency('The system is down!');
                -->
            </buildingLogStacks>
        </configuration>
        <writingLogMessages>
            You may write information to the logs using the Log facade. As previously mentioned, the logger provides the
            eight logging levels defined in the RFC 5424 specification:

            <!--
                Log::emergency($message);
                Log::alert($message);
                Log::critical($message);
                Log::error($message);
                Log::warning($message);
                Log::notice($message);
                Log::info($message);
                Log::debug($message);
            -->

            So, you may call any of the se methods to log a message for the corresponding level. By default, the message
            will be written to the default log channel as configured by your config/logging.php configuration file:

            <!--
                namespace App\Http\Controllers;

                use App\Http\Controllers\Controller;
                use App\User;
                use Illuminate\Support\Facades\Log;

                class UserController extends Controller
                {
                    /**
                     * Show the profile for the given user.
                     *
                     * @param  int  $id
                     * @return Response
                     */
                    public function showProfile($id)
                    {
                        Log::info('Showing user profile for user: '.$id);

                        return view('user.profile', ['user' => User::findOrFail($id)]);
                    }
                }

            -->

            Contextual Information
            An array of contextual data may also be passed to the log methods. This contextual data will be formatted
            and displayed with the log message:

            <!--
                Log:info('User failed to login.', ['id' => $user->id]);
            -->

            <writingToSpecificChannels>
                Sometimes you may wish to log a message to a channel other than your application's default channel.
                You may use the channel method on the Log facade to retrieve and log to any channel defined in your
                configuration file:

                <!--
                    Log::channel('slack')->info('Something happened!');
                -->

                If you would like to create an on-demand logging stack consisting of multiple channels, you may use the
                stack method:

                <!--
                    Log::stack(['single', 'slack'])->info('Something happened!');
                -->

            </writingToSpecificChannels>
        </writingLogMessages>
        <advancedMonologChannelCustomization>
            <customizingMonologForChannels>
                Sometimes you may need complete control over how Monolog is configured for an existing channel.  For
                example, you may want to configure a custom Monolog FormatterInterface implementation for a    given
                channel's handlers. To get started, define a tap array on teh channel's configuration. The tap array
                should contain a list of classes that should have an opportunity to customize (or "tap" into)    the
                Monolog instance after it is created:

                <!--
                    'single' => [
                        'driver' => 'single',
                        'tap' => [App\Logging\CustomizeFormatter::class],
                        'path' => storage_path('logs/laravel.log'),
                        'level' => 'debug',
                    ],
                -->

                Once you have configured the tap option on your channel, you're ready to define the class that will
                customize your Monolog instance. This class only needs a single method  __invoke, which receives an
                Illuminate\Log\Logger instance. The Illuminate\Log\Logger instance proxies all method calls to  the
                underlying Monolog instance:

                <!--
                    namespace App\Logging;

                    class CustomizeFormatter
                    {
                        /**
                         * Customize the given logger instance.
                         *
                         * @param  \Illuminate\Log\Logger  $logger
                         * @return void
                         */
                        public function __invoke($logger)
                        {
                            foreach ($logger->getHandlers() as $handler) {
                                $handler->setFormatter(...);
                            }
                        }
                    }
                -->

                <NOTE>
                    All of your "tap" classes are resolved by the service container, so any constructor dependencies
                    they require will automatically be injected.
                </NOTE>

            </customizingMonologForChannels>
            <creatingMonologHandlerChannels>
                Monolog has a variety of available handlers. In some cases, the type of logger your wish to create    is
                merely a Monolog driver with an instance of a specific handler. These channels can be created using  the
                monolog driver. When using the monolog driver, the handler configuration option is used to specify which
                handler will be instantiated. Optionally, any constructor parameters the handler needs may be  specified
                using the with configuration option:

                <!--
                    'logentries' => [
                        'driver'  => 'monolog',
                        'handler' => Monolog\Handler\SyslogUdpHandler::class,
                        'with' => [
                            'host' => 'my.logentries.internal.datahubhost.company.com',
                            'port' => '10000',
                        ],
                    ],
                -->

                Monolog Formatters
                When using the monolog driver, the Monolog LineFormatter will be used as the default formatter. However
                you may customize the type of formatter passed to the handler using the formatter and    formatter_with
                configuration options:

                <!--
                    'browser' => [
                        'driver' => 'monolog',
                        'handler' => Monolog\Handler\BrowserConsoleHandler::class,
                        'formatter' => Monolog\Formatter\HtmlFormatter::class,
                        'formatter_with' => [
                            'dateFormat' => 'Y-m-d',
                        ],
                    ],
                -->

                If you are using a Monolog handler that is capable of providing its own formatter, you may set the value
                of the formatter configuration option to default:

                <!--
                    'newrelic' => [
                        'driver' => 'monolog',
                        'handler' => Monolog\Handler\NewRelicHandler::class,
                        'formatter' => 'default',
                    ],
                -->

            </creatingMonologHandlerChannels>
            <CreatingCannelsViaFactories>
                If you would like to define an entirely custom channel in which you have full control over  Monolog's
                instantiation and configuration, you may specify a custom driver type in you config/logging.php  file
                Your configuration should include a via option to point to the factory class which will be invoked to
                create the Monolog instance:

                <!--
                    'channels' => [
                        'custom' => [
                            'driver' => 'custom',
                            'via' => App\Logging\CreateCustomLogger::class,
                        ],
                    ],
                -->

                Once you have configured the custom channel, you're ready to define the clas that will create your
                Monolog instance. This class only needs a single method: __invoke, which should return the Monolog
                instance:

                <!--
                    namespace App\Logging;

                    use Monolog\Logger;

                    class CreateCustomLogger
                    {
                        /**
                         * Create a custom Monolog instance.
                         *
                         * @param  array  $config
                         * @return \Monolog\Logger
                         */
                        public function __invoke(array $config)
                        {
                            return new Logger(...);
                        }
                    }
                -->
            </CreatingCannelsViaFactories>
        </advancedMonologChannelCustomization>
    </logging>
</theBasics>

<frontend>
    <bladeTemplates>
        <introduction>
            Blade is teh simple, yet powerful templating engine provided with Laravel. Unlike other popular   PHP
            templating engines, Blade does not restrict you from using plain PHP code in your views. In fact, all
            Blade views are compiled into plain PHP code and cached until they are modified, meaning Blade   adds
            essentially zero overhead to your application. Blade view files use the .blade.php file extension and
            are typically stored in the resources/views directory.
        </introduction>
        <templateInheritance>
            <definingALayout>
                Two of the primary benefits of using Blade are template inheritance and sections. To get started, let's
                take a look at a simple example. First, we will examine a "master" page layout. Since most          web
                applications maintain the same general layout across various pages, it's conveninet to define      this
                layout as a single Blade view:

                <!-- Stored in resources/views/layouts/app.blade.php -->
                <!--
                    <html>
                        <head>
                            <title>App Name - @yield('title')</title>
                        </head>
                        <body>
                            @section('sidebar')
                                This is the master sidebar.
                            @show

                            <div class="container">
                                @yield('content')
                            </div>
                        </body>
                    </html>
                -->

                As you can see, this file contains typical HTML mark-up. However, take note of the @section and   @yield
                directives. The @section directive, as the name implies, defines a section of content, while the  @yield
                directive is used to display the contents of a given section. Now that we have defined a lay out for our
                application, let's define a child page that inherits the layout.
            </definingALayout>
            <extendingALayout>
                When defining a child view use the Blade @extends directive to specify which layout the child view should
                inherit. Views which extend a Blade layout may inject content into the layout's sections using   @section
                directive. Remember, as seen in the example above the contents of these sections will be displayed in the
                layout using @yield:

                <!-- Stored in resources/views/child.blade.php -->
                <!--
                    @extends('layouts.app')

                    @section('title', 'Page Title')

                    @section('sidebar')
                        @parent

                        <p>This is appended to the master sidebar.</p>
                    @endsection

                    @section('content')
                        <p>This is my body content.</p>
                    @endsection
                -->

                In this example, the sidebar section is utilizing the @parent directive to append (rather than overwriting)
                content to the layout's sidebar. The @parent directive will be replaced by the content of the layout   when
                the view is rendered.

                <NOTE>
                    Contrary to the previous example, this sidebar section ends with @endsection instead of
                    @show. The @endsection directive will only define a section while @show will define and
                    immediately yield the section.
                </NOTE>

                The @yield directive also accepts a default value as its second parameter. This value will be
                rendered if the section being yielded is undefined:

                <!--
                    @yield('content', View::make('view.name'))
                -->

                Blade views may be returned from routes using the global view helper:

                <!--
                    Route::get('blade', function () {
                        return view('child');
                    })
                -->

            </extendingALayout>
        </templateInheritance>
        <componentsAndSlots>
            Components and slots provide similar benefits to sections and layouts; however, some may find the mental
            model of components and slots easier to understand. First, let's imagine a reusable "alert" component we
            would like to reuse throughout our application:

            <!-- /resources/views/alert.blade.php -->

            <!--
                <div class="alert alert-danger">
                    {{ $slot }}
                </div>
            -->

            The {{  $slot  }} variable will contain the content we wish to inject into the component. Now, to construct
            this component, we can use the @component Blade directive:

            <!--
                @component('alert')
                    <strong>Whoops!</strong> Something went wrong!
                @endcomponent
            -->

            To instruct Laravel to load the first view that exists from a given array of possible views for the component
            you may use the componentFirst directive:

            <!--
                @componentfirst(['custom.alert', 'alert'])
                    <strong>Whoops!</strong> Something went wrong!
                @endcomponentfirst
            -->

            Sometimes it is helpful to define multiple slots for a component. Let's modify our alert component to  allow
            for the injection of a "title". Named slots may be displayed by echoing the variable that matches their name

            <!-- /resources/views/alert.blade.php -->

            <!--
                <div class="alert alert-danger">
                    <div class="alert-title">{{ $title }}</div>

                    {{ $slot }}
                </div>
            -->

            Now, we can inject content into the named slot using the @slot directive. Any content not within a @slot
            directive will be passed to the component in the $slot variable:

            <!--
                @component('alert')
                    @slot('title')
                        Forbidden
                    @endslot

                    You are not allowed to access this resource!
                @endcomponent
            -->

            Passing Additional Data To Components
            Sometimes you may need to pass additional data to a component. For this reason, you can pass an array of data
            as the second argument to the @component directive. All of the data will be made available to the   component
            template as variables:

            <!--
                @component('alert', ['foo' => 'bar'])

                @endcomponent
            -->

            Aliasing Components
            If your Blade components are stored in a subdirectory, you may wish to alias them for easier access. For
            example, imagine a Blade component that is stored at resources/views/components/alert.blade.php You  may
            use the component method to alias the component from components.alert to alert. Typically, this   should
            be done in the boot method of your AppServiceProvider:

            <!--
                use Illuminate\Support\Facades\Blade;

                Blade::component('component.alert', 'alert');
            -->

            Oncee the component has been aliased, you may render it using a directive:

            <!--
                @alert(['type' => 'danger'])
                    You are not allowed to access this resource!
                @endalert
            -->

            You may omit the component parameters if it has no additional slots:

            <!--
                @alert
                    You are not allowed to access this resource!
                @endalert
            -->


        </componentsAndSlots>
        <displayingData>
            You may display data passed to your Blade views by wrapping the variable in curly braces. For exampl:

            <!--
                Route::get('greeting', function () {
                    return view('welcome', ['name' => 'Samantha']);
                });
            -->

            You may display the contents of the name variable like so:

            <!--
                {{ $name }}
            -->

            <NOTE>
                Blade {{  }} statements are automatically sent through PHP's htmlspecialchars function to
                prevent XSS attacks.
            </NOTE>

            You are not limited to displaying the contents of the variables passed to the view. You may also echo the
            results of any PHP function. In fact, you can put any PHP code you wish inside of a Blade echo statement:

            <!--
                The current UNIX timestamp is {{ time() }}.
            -->

            Displaying Unescaped Data
            By default, Blade {{}} statements are automatically sent through PHP's htmlspecialchars function to prevent
            XSS attacks. If you do not want your data to be escaped, you may use the following syntax:

            <!--
                Hello, {!! $name !!}.
            -->

            <NOTE>
                Be very careful when echoing content that is supplied by users of your application. Always use the
                escaped, double curlly brace syntax to prevent XSS attacks when displaying user supplied data.
            </NOTE>

            Rendering JSON
            Sometimes you may pass an array to your view with the intention of rendering it as JSON in order to
            initialize a JavaScript variable. For example:

            <!--
                <script>
                    var app = <?php// echo json_encode($array); ?>;
                </script>
            -->

            However, instead of manually caling json_encode, you may use the @json Blade directive. The json directive
            accepts the same arguments as PHP's json_encode function:

            <!--
                <script>
                    var app = @json($array);

                    var app = @json($array, JSON_PRETTY_PRINT);
                </script>
            -->

            <NOTE>
                You should only use the @json directive to render existing variables as JSON. The Blade templating is
                based on regular expressions and attempts to pass a complex expression to the directive may     cause
                unexpected failures.
            </NOTE>

            The @json directive is also useful for seeding Vue components or data-* attributes:

            <!--
                <example-component :some-prop='@json($array)'></example-component>
            -->

            <NOTE>
                Using @json in element attributes requires that it be surrounded by single quotes.
            </NOTE>

            HTML Entity Encoding
            By default, Blade (and the Laravel e helper) will double encode HTML entities. If you would like to   disable
            double encoding, call the Blade::withoutDoubleEncoding method from the boot method of your AppServiceProvider

            <!--
                Blade::withoutDoubleEncoding();
            -->

            <bladeAndJavaScriptFrameworks>
                Since many JavaScript frameworks also use "curly" braces to indicate a given expression should     be
                displayed in the browser, you may use the @ symbol to inform the Blade rendering engine on expression
                should remain untouched. For example:

                <!--
                    <h1>Laravel</h1>

                    Hello, @{{ name }}.
                -->

                In this example, the @ symbol will be removed by Blade; however, {{ name }} expression will remain
                untouched by the Blade engine, allowing it to instead be rendered by your JavaScript framework.

                The @verbatim Directive
                If you are displaying JavaScript variables in a large portion of your template, you may wrap the HTML in
                the @verbatim directive so that you do not have to prefix each Blade echo statement with an @ symbol:

                <!--
                    @verbatim
                        <div class="container">
                            Hello, {{ name }}.
                        </div>
                    @endverbatim
                -->

            </bladeAndJavaScriptFrameworks>
        </displayingData>
        <controlStructures>
            In addition to template inheritance and displaying data, Blade also provides convenient shortcuts for common
            PHP control structures, such as conditional statements and loops. Thesee shortcuts provide a very     clean,
            terse way of working with PHP control structures, while also remaining familiar to their PHP counterparts.

            <ifStatements>
                You may construct if statements using the @if, @elseif, @else, and @endif directives. These directives
                function identically to thir PHP counterparts:

                    <!--
                        @if (count($records) === 1)
                            I have one record!
                        @elseif (count($records) > 1)
                            I have multiple records!
                        @else
                            I don't have any records!
                        @endif
                    -->

                For convenience, Blade also provides an @unless directive:

                <!--
                    @unless(Auth::check())
                        you are not signed in.
                    @endunless
                -->

                In addition to the conditional directives already discussed, the @isset and @empty directives may
                be used as convenient shortcuts for their respective PHP function:

                <!--
                    @isset($records)
                        // $records is defined and is not null...
                    @endisset

                    @empty($records)
                        // $records is "empty"...
                    @endempty
                -->

                Authentication Directives
                The @auth and @guest directives may be used to quickly determine if the current user is authenticated or
                is a guest:

                <!--
                    @auth
                        // The user is authenticated...
                    @endauth

                    @guest
                        // The user is not authenticated...
                    @endguest
                -->

                If needed, you may specify the authentication guard that should be checked when using the @auth
                and @guest directives:

                <!--
                    @auth('admin')
                        // The user is authenticated...
                    @endauth

                    @guest('admin')
                        // The user is not authenticated...
                    @endguest
                -->

                Section Directives
                You may check if a section has content using the @hasSection directive:

                <!--
                    @hasSection('navigation')
                        <div class="pull-right">
                            @yield('navigation')
                        </div>

                        <div class="clearfix"></div>
                    @endif
                -->

            </ifStatements>
            <switchStatements>
                Switch statements can be constructed using the @switch, @case, @break, @default and @endswitch directives:

                <!--
                    @switch($i)
                        @case(1)
                            First case...
                            @break

                        @case(2)
                            Second case...
                            @break

                        @default
                            Default case...
                    @endswitch
                -->
            </switchStatements>
            <loops>
                In addition to conditional statements, Blade provides simple directives for working with PHP's loop
                structures. Again, each of these directives functions identically to their PHP counterparts:

                <!--
                    @for ($i = 0; $i < 10; $i++)
                        The current value is {{ $i }}
                    @endfor

                    @foreach ($users as $user)
                        <p>This is user {{ $user->id }}</p>
                    @endforeach

                    @forelse ($users as $user)
                        <li>{{ $user->name }}</li>
                    @empty
                        <p>No users</p>
                    @endforelse

                    @while (true)
                        <p>I'm looping forever.</p>
                    @endwhile
                -->

                <NOTE>
                    When looping, you may use the loop variable to gain valuable information about the loop, such as
                    whether you are in the first or last iteration through the loop.
                </NOTE>

                You may also include the condition with the directive declaration in one line:

                <!--
                    @foreach ($users as $user)
                        @continue($user->type == 1)

                        <li>{{ $user->name }}</li>

                        @break($user->number == 5)
                    @endforeach
                -->

            </loops>
            <theLoopVariable>
                When looping, a $loop variable will be available inside of your loop. This variable provides access to
                some useful bits of information such as the current loop index and whether this is the first or   last
                iteration through the loop:

                <!--
                    @foreach ($users as $user)
                        @if ($loop->first)
                            This is the first iteration.
                        @endif

                        @if ($loop->last)
                            This is the last iteration.
                        @endif

                        <p>This is user {{ $user->id }}</p>
                    @endforeach
                -->

                If you are in a nested loop, you may access the parent loop's $loop variable via the parent property:

                <!--
                    @foreach ($users as $user)
                        @foreach ($user->posts as $post)
                            @if ($loop->parent->first)
                                This is first iteration of the parent loop.
                            @endif
                        @endforeach
                    @endforeach
                -->

                The $loop variable also contains a variety of other useful properties:

                Property                    Description
                $loop->index                The index of the current loop iteration (starts at 0).
                $loop->iteration            The current loop iteration (starts at 1).
                $loop->remaining            The iterations remaining in the loop.
                $loop->count                The total number of items in the array being iterated.
                $loop->first                Whether this is the first iteration through the loop.
                $loop->last                 Whether this is the last  iteration through the loop.
                $loop->even                 Whether this is an  even  iteration through the loop.
                $loop->odd                  Whether this is an  odd   iteration through the loop.
                $loop->depth                The nesting level of the current loop.
                $loop->parent               When in a nested loop, the parent's loop variable.

            </theLoopVariable>
            <comments>
                Blade also allows you to define comments in your views. However, unlike HTML comments,
                Blade comments are not included in the HTML returned by your application:

                <!--
                    {{-- This comment will not be present in the rendered HTML --}}
                -->
            </comments>
            <PHP>
                In some situations, it's useful to embed PHP code into your views. You can use the Blade @php
                directive to execute a block of plain PHP within your template:

                <!--
                    @php
                        //
                    @endphp
                -->

                <NOTE>
                    While Blade provides this feature, using it frequently may be a signal that you have too much
                    logic embedded within your template.
                </NOTE>
            </PHP>
        </controlStructures>
        <forms>
            <CSRFField>
                Anytime you define an HTML form in your application, you should include a hidden CSRF token field in the
                form so that the CSRF protection middleware can validate the request. You may use the @csrf        Blade
                directive to generate the token field.
            </CSRFField>
            <methodField>
                Since HTML forms can't make PUT, PATCH or DELETE request, you will need to add a hidden _method field to
                spoof these HTTP verbs. The @method Blade directive can create this field for you:

                <!--
                    <form action="/foo/bar" method="POST">
                        @method('PUT')

                        ...
                    </form>
                -->
            </methodField>
            <validationErrors>
                The @error directive may be used to quickly check if validation error messages exist for a given
                attribute. Within an @error directive, you may echo the $message variable to display the   error
                message:

                <!-- /resources/views/post/create.blade.php -->
                <!--
                    <label for="title">Post Title</label>

                    <input id="title" type="text" class="@error('title') is-invalid @enderror">

                    @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                -->

                You may pass the name of a specific error bag as the second parameter to the @error direvtive to
                retrieve validation error messages on pages containing multiple forms:

                <!--
                    @error('email', 'login')
                -->
            </validationErrors>
        </forms>
        <includingSubviews>
            Blade's @include directive allows you to include a Blade view from within another view. All variables that
            are available to the parent view will be made available to the included view:

            <!--
                <div>
                    @include('shared.errors')

                    <form>
                        //
                    </form>
                </div>
            -->

            Even thought the included view will inherit all data available in the parent view, you may also pass
            an array of extra data to the included view:

            <!--
                @include('view.name', ['some' => 'data'])
            -->

            If you attempt to @include a view which does not exist, Laravel will throw an error. If you would like
            to include a view that may or may not be present, you should use the @includeIf directive:

            <!--
                @includeIf('view.name', ['some' => 'data'])
            -->

            If you would like to @include a view if a given boolean expression evaluates to true, you may use the
            @includeWhen directive:

            <!--
                @includeWhen($boolean, 'view.name', ['some' => 'data'])
            -->

            If you would like to @include a view if a given boolean expression evaluates to false, you may use the
            @includUnless directive:

            <!--
                @includeUnless($boolean, 'view.name', ['some' => 'data'])
            -->

            To include the first view that exists from a given array of views, you may use the includeFirst directive:

            <!--
                @includeFirst(['custom.admin', 'admin'], ['some' => 'data'])
            -->

            <NOTE>
                You should avoid using the __DIR__ and __FILE__ constants in your Blade views, since they will refer
                to the location of the cached, compiled view.
            </NOTE>

            Aliasing Includes
            If your Blade includes are stored in a subdirectory you may wish to alias them for easier    access.
            For example, imagine a Blade include that is stored at resources/views/includes/input.blade.php with
            the following content:

            <!--
                <input type="{{ $type ?? 'text' }}">
            -->

            You may use the include method to alias the include from includes.input to input. Typically, this should
            be done in the boot method of your AppServiceProvider:

            <!--
                use Illuminate\Support\Facades\Blade;

                Blade::include('includes.input', 'input');
            -->

            Once the include has been aliased, you may render it using the alias name as the Blade directive:

            <!--
                @input(['type' => 'email'])
            -->

            <renderingViewsForCollections>
                You may combine loops and includes into one line with Blade's @each directive:

                <!--
                    @each('view.name', $jobs, 'job')
                -->

                The first argument is the view partial to render for each element in the array or collection. The   second
                argument is the array or collection you wish to iterate over, while the third argument is the     variable
                name that will be assigned to the current iteration within the view. So, for example, ify ou are iterating
                over an array of jobs, typically you will want to access each job as a job bvariable within your      view
                partial. The key for the current iteration will be available as the key variable within your view partial.

                You may also pass a fourth argument to the @each directive. This argument determines the view that
                will be rendered if the given array is empty.

                <!--
                    @each('view.name', $jobs, 'job', 'view.empty')
                -->

                <NOTE>
                    Views rendered via @each do not inherit the variables from the parent view. If the child view requires
                    these variables, you should use @foreach and @include instead.
                </NOTE>
            </renderingViewsForCollections>
        </includingSubviews>
        <stacks>
            Blade allows you to push to named stacks which can be rendered somewhere else in another view or layout.
            This can be particularly useful for specifying any JavaScript libraries required by your child views:

            <!--
                @push('scripts')
                    <script src="/example.js"></script>
                @endpush
            -->
            You may push to a stack as many times as needed. To render the complete stack contents, pass the name of the
            stack to the @stack directive:

            <!--
                <head>
                    @stack('scripts')
                </head>
            -->

            If you would like to prepend content onto the beginning of a stack, you should use the @prepend directive:

            <!--
                @push('scripts')
                    This will be second...
                @endpush

                // Later..

                @perpend('scripts')
                    this will be first...
                @endprepend
            -->
        </stacks>
        <serviceInjection>
            The @inject directive may be used to retrieve a service from the Laravel service container. The first
            argument passed to @inject is the name of the variable the service will be placed into, while     the
            second argument is the class or interface name of the service you wish to resolve:

            <!--
                @inject('metrics', 'App\Services\MetricsService')

                <div>
                    Monthly Revenue: {{ $metrics->monthlyRevenue() }}.
                </div>
            -->
        </serviceInjection>
        <extendingBlade>
            Blade allows you to define your own custom directives using the directive method. When the Blade  compiler
            encounters the custom directive, it will call the provided callback with the expression that the directive
            contains. The following example creates a @datetime($var) directive which formats a given $var,      which
            should be an instance of DateTime:

            <!--

                namespace App\Providers;

                use Illuminate\Support\Facades\Blade;
                use Illuminate\Support\ServiceProvider;

                class AppServiceProvider extends ServiceProvider
                {
                    /**
                     * Register any application services.
                     *
                     * @return void
                     */
                    public function register()
                    {
                        //
                    }

                    /**
                     * Bootstrap any application services.
                     *
                     * @return void
                     */
                    public function boot()
                    {
                        Blade::directive('datetime', function ($expression) {
                            return "<?php echo ($expression)->format('m/d/Y H:i'); ?>";
                        });
                    }
                }
            -->

            As you can, see we will chain the format method onto whatever expression is passed into the directive.
            So, in this example, the final PHP generated by this directive will be:

            <!--
                <?php echo ($var)->format('m/d/Y H:i'); ?>
            -->

            <NOTE>
                After updating the logic of a Blade directive, you will need to delete all of the cached Blade views.
                The cached Blade views may be removed using the view:clear artisan command.
            </NOTE>

            <customIfStatements>
                Programming a custom directive is sometimes more complex than necessary when defining simple, custom
                conditional statements. For that reason, Blade provides a Blade:::if method which allows you      to
                quickly define custom conditional directives using Closures. For example, let's define a      custom
                conditional that checks the current application environment. We may do this in the boot method    of
                our AppServiceProvider:

                <!--
                    use Illuminate\Support\Facades\Blade;

                    /**
                     * Bootstrap any application services.
                     *
                     * @return void
                     */
                    public function boot()
                    {
                        Blade::if('env', function ($environment) {
                            return app()->environment($environment);
                        });
                    }
                -->

                Once ethe custom conditional has been defined, we can easily use it on our templates:

                <!--
                    @env('local')
                        // The application is in the local environment...
                    @elseenv('testing')
                        // The application is in the testing environment...
                    @else
                        // The application is not in the local or testing environment...
                    @endenv

                    @unlessenv('production')
                        // The application is not in the production environment...
                    @endenv
                -->
            </customIfStatements>
        </extendingBlade>
    </bladeTemplates>

    <localization>
        <introduction>
            Laravel's localization features provide a convenient way to retrieve strings in various languages, allowing
            you to easily support multiple languages within your application. Language strings are stored in      files
            within the resources/lang directory. Within this directory there should be a subdirectory for each language
            supported by the application:

            <!--
                /resources
                    /lang
                        /en
                            messages.php
                        /ar
                            messages.php
            -->

            All language files return an array of keyed strings. For example:

            <!--
                return [
                    'welcome' => 'Welcome to our application'
                ];

            -->

            <NOTE>
                For languages that differ by territory, you should name the language directories according to
                the ISO 15897. For example, "en_GB" should be used for British English rather than "en-gb".
            </NOTE>
            <configuringTheLocale>
                The default language for your application is stored in the config/app.php file. You may modify
                this value to suit the needs of your application. You may also change the active language   at
                runtime using the setLocale method on The App facade:

                <!--
                    Route::get('welcome/{locale}', function ($locale) {
                        App::setLocale($locale);
                    });
                -->

                You may configure a "fallback language", which will be used when the active language does not
                contain a given translation string. Like the default language, the fallback language is  also
                configured in the config/app.php configuration file:

                <!--
                    'fallback_locale' => 'en',
                -->

                Determining The Current Locale
                You may use the getLocale and isLocale methods on the App facade to determin the current locale or
                check if the locale is a give value:

                <!--
                    $locale = App::getLocale();

                    if (App::isLocale('en')) {
                        //
                    }
                -->
            </configuringTheLocale>
        </introduction>
        <definingTranslationStrings>
            <usingShortKeys>
                Typically, translation strings are stored in files within the reources/lang directory. Within this
                directory there should be a subdirectory for each language supported by the application:

                <!--
                    /resources
                        /lang
                            /en
                                messages.php
                            /es
                                messages.php
                -->

                All language files return an array of keyed strings.
            </usingShortKeys>
            <usingTranslationStringsAsKeys>
                For applications with heavy translation requirements, defining every string with a "short key"   can
                become quickly confusing when referencing them in your views. For this reason, Laravel also provides
                support for defining translation strings using the "default" translation of the string as the key.

                Translation files that use translation strings as keys are stored as JSON files in the resources/lang
                directory. For example, if your application has a Spanish translation, you should create            a
                resources/lang/es.json file:

                <!--
                    {
                        "I love programming.": "Me encanta programar."
                    }
                -->
            </usingTranslationStringsAsKeys>
        </definingTranslationStrings>
        <retrievingTranslationStrings>
            You may retrieve lines from language files using the __ helper function. The __ method accepts the file and
            key of the translation string as its first argument. For example, let's retrieve the welcom     translation
            string from the resources/lang/messages.php language file:

            <!--
                echo __('messages.welcome');

                echo __('I love programming.');
            -->

            If you are using the Blade templating engine, you may use the {{}} syntax to echo the translation string
            or use the @lang directive:

            <!--
                {{ __('messages.welcome') }}

                @lang('messages.welcome')
            -->

            If the specified translation string does not exist, the __ function will return the translation   string
            key. So, using the esample above, the __function would return messages.welcome if the translation string
            does not exist.

            <NOTE>
                The @lang directive does not escape any output. You are fully responsible for escaping your own
                output when using this directive
            </NOTE>
            <replacingParametersInTranslationStrings>
                If you wish, you may define placeholders in your translation strings. All placceholders are prefixed
                with a : . For example, you may define a welcome message with a placeholder name:

                <!--
                    'welcome' => 'Welcome, :name',
                    echo __('messages.welcome', ['name' => 'dayle']);
                -->

                If your placeholder contains all capital letters, or only has its first letter capitalize, the translated
                value will be capitalized accordingly:

                <!--
                    'welcome' => 'Welcome, :NAME', // Welcome, DAYLE
                    'goodbye' => 'Goodbye, :Name', // Goodbye, Dayle
                -->
            </replacingParametersInTranslationStrings>
            <pluralization>
                Pluralization is a complex problem, as different languages have a variety of complex rules for pluralization.
                By using a "pipe" character, you may distinguish singular and plural forms of a string
            </pluralization>
        </retrievingTranslationStrings>
        <overridingPackageLanguageFiles></overridingPackageLanguageFiles>
    </localization>
    <frontendScaffolding></frontendScaffolding>
    <compilingAssets></compilingAssets>
</frontend>

<security>
    <authentication></authentication>
    <APIAuthentication></APIAuthentication>
    <authorization></authorization>
    <emailVerification></emailVerification>
    <encryption></encryption>
    <hashing></hashing>
    <passwordReset></passwordReset>
</security>

<diggingDeeper>
    <artisanConsole></artisanConsole>
    <broadcasting></broadcasting>
    <cache></cache>
    <collections></collections>
    <events></events>
    <fileStorage></fileStorage>
    <helpers></helpers>
    <mail></mail>
    <notifications></notifications>
    <packageDevelopment></packageDevelopment>
    <queues></queues>
    <taskScheduling></taskScheduling>
</diggingDeeper>

<database>
    <gettingStarted></gettingStarted>
    <queryBuilder></queryBuilder>
    <pagination></pagination>
    <migrations></migrations>
    <seeding></seeding>
    <redis></redis>
</database>

<eloquentORM>
    <gettingStarted></gettingStarted>
    <relationships></relationships>
    <collections></collections>
    <mutators></mutators>
    <APIResources></APIResources>
    <serialization></serialization>
</eloquentORM>

<testing>
    <gettingStarted></gettingStarted>
    <HTTPTests></HTTPTests>
    <consoleTests></consoleTests>
    <browserTests></browserTests>
    <Database></Database>
    <mocking></mocking>
</testing>

<officialPackages>
    <cashier></cashier>
    <dusk></dusk>
    <envoy></envoy>
    <horizon></horizon>
    <passport></passport>
    <scout></scout>
    <socialite></socialite>
    <telescope></telescope>
</officialPackages>















































