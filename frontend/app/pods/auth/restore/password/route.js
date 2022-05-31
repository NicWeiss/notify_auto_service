import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';

export default class RestorePasswordRoute extends Route {
  @service store;
  @service session;
  @tracked object = null;

  beforeModel() {
    if (this.session.isAuthenticated) {
      this.transitionTo('manage.categories');
    }
  }

  model(params) {
    return params.restore_id;
  }

  @action
  toLogin() {
    this.transitionTo('auth.login');
  }
}
