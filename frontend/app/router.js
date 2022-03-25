import EmberRouter from '@ember/routing/router';
import config from 'frontend/config/environment';

export default class Router extends EmberRouter {
  location = config.locationType;
  rootURL = config.rootURL;
}

Router.map(function () {

  this.route('auth', { path: '/' });

  this.route('auth', function () {
    this.route('login');
    this.route('logout');
    this.route('signup');
    this.route('restore', function () {
      this.route('query', { path: '/' });
      this.route('password', { path: '/:restore_id/password' });
    });
  });

  this.route('manage', function () {
    this.route('categories', function () {
      this.route('new');
    });

    this.route('settings', function () {
    });
  });

  this.route('404', { path: '/*path' });
});
