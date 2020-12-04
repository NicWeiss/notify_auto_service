import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';

export default class AuthRoute extends Route {
  @service store;
  @service session;
  @tracked object = null;

  beforeModel() {
    if (this.session.isAuthenticated) {
      this.transitionTo('manage.notifications');
    }
  }

  async model() {
    this.object = {};
    return this.object
  }

  @action
  onLoginDone() {
    window.location.reload(true);
  }

}
