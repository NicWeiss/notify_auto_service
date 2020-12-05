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
  });

  this.route('manage', function () {
    this.route('notifications', function () {
      this.route('new');
      this.route('edit', {path: '/:notify_id/edit'});
      this.route('show', { path: '/:notify_id' });
    });

    this.route('acceptors', function () {
      this.route('list');
      this.route('new');
    });
  });

});
