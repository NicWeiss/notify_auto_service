import EmberRouter from '@ember/routing/router';
import config from 'frontend/config/environment';

export default class Router extends EmberRouter {
  location = config.locationType;
  rootURL = config.rootURL;
}

Router.map(function() {

  this.route('auth', { path: '/' });

  this.route('auth', function () {
    this.route('login');
    this.route('logout');
    this.route('signup');
  });
  
  this.route('manage', function () {
    this.route('list');
    this.route('new');
  });
});
