<?php namespace Crhayes\BladePartials;

class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->registerBladeExtensions();
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory()
    {
        $this->app->bindShared('view', function($app)
        {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];

            $env = new Factory($resolver, $finder, $app['events']);

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $env->setContainer($app);

            $env->share('app', $app);

            return $env;
        });
    }

    /**
     * Register custom blade extensions.
     * 
     * @return void
     */
    protected function registerBladeExtensions()
    {
        $blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();

        $blade->extend(function($value, $compiler)
        {
            $pattern = $this->createOpenMatcher('partial');

            return preg_replace(
                $pattern, 
                '$1<?php $__env->renderPartial$2, get_defined_vars(), function($file, $vars) use ($__env) { 
                    $vars = array_except($vars, array(\'__data\', \'__path\')); 
                    extract($vars); ?>', 
                $value
            );
        });

        $blade->extend(function($value, $compiler)
        {
            $pattern = $this->createPlainMatcher('endpartial');

            return preg_replace($pattern, '$1<?php echo $__env->make($file, $vars)->render(); }); ?>$2', $value);
        });

        $blade->extend(function($value, $compiler)
        {
            $pattern = $this->createMatcher('block');

            return preg_replace($pattern, '$1<?php $__env->startBlock$2; ?>', $value);
        });

        $blade->extend(function($value, $compiler)
        {
            $pattern = $this->createPlainMatcher('endblock');

            return preg_replace($pattern, '$1<?php $__env->stopBlock(); ?>$2', $value);
        });

        $blade->extend(function($value, $compiler)
        {
            $pattern = $this->createMatcher('render');

            return preg_replace($pattern, '$1<?php echo $__env->renderBlock$2; ?>', $value);
        });
    }

    /* Get the regular expression for a generic Blade function.
    *
    * @param  string  $function
    * @return string
    */
    public function createMatcher($function)
    {
        return '/(?<!\w)(\s*)@'.$function.'(\s*\(.*\))/';
    }

    /**
    * Get the regular expression for a generic Blade function.
    *
    * @param  string  $function
    * @return string
    */
    public function createOpenMatcher($function)
    {
        return '/(?<!\w)(\s*)@'.$function.'(\s*\(.*)\)/';
    }
    /**
    * Create a plain Blade matcher.
    *
    * @param  string  $function
    * @return string
    */
    public function createPlainMatcher($function)
    {
        return '/(?<!\w)(\s*)@'.$function.'(\s*)/';
    }

}
