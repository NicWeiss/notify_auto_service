import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class LogoutRoute extends Route {
  @service store;
  @service session;
  @tracked object = null;

  afterModel() {
    this.session.invalidate();
    this.transitionTo('auth.login');
  }

}
