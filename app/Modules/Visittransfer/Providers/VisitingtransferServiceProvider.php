<?php
namespace App\Modules\Visittransfer\Providers;

use App;
use Config;
use Lang;
use View;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Policies\ApplicationPolicy;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use App\Modules\Visittransfer\Observers\ApplicationObserver;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class VisittransferServiceProvider extends ServiceProvider
{
	protected $policies = [
		Application::class => ApplicationPolicy::class,
	];

	/**
	 * Register the Visittransfer module service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// This service provider is a convenient place to register your modules
		// services in the IoC container. If you wish, you may make additional
		// methods or service providers to keep the code more focused and granular.
		App::register('App\Modules\Visittransfer\Providers\RouteServiceProvider');

		$this->registerNamespaces();
		$this->registerComposers();
		$this->registerCommands();
	}

	public function boot(GateContract $gate){
		parent::registerPolicies($gate);

		Application::observe(new ApplicationObserver());
	}

	/**
	 * Register the Visittransfer module resource namespaces.
	 *
	 * @return void
	 */
	protected function registerNamespaces()
	{
		Lang::addNamespace('visittransfer', realpath(__DIR__.'/../Resources/Lang'));

		View::addNamespace('visittransfer', base_path('resources/views/vendor/visittransfer'));
		View::addNamespace('visittransfer', realpath(__DIR__.'/../Resources/Views'));
	}

	/**
	 * Register the Visittransfer module composers.
	 *
	 * @return void
	 */
	protected function registerComposers(){
		//View::composer("visittransfer::admin.partials");
	}

	protected function registerCommands(){
		// Commands.statistics.daily
		$this->app->singleton("visittransfer::commands.statistics.daily", function($app){
			return $app['\App\Modules\Visittransfer\Console\Commands\StatisticsDaily'];
		});
		$this->commands("visittransfer::commands.statistics.daily");


		// commands.applications.cleanup
		$this->app->singleton("visittransfer::commands.applications.cleanup", function($app){
			return $app['\App\Modules\Visittransfer\Console\Commands\ApplicationsCleanup'];
		});
		$this->commands("visittransfer::commands.applications.cleanup");
	}
}
