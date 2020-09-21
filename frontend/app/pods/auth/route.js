import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class AuthRoute extends Route {
  @service store;
  @service auth;
  @tracked object = null;

  beforeModel() {
    if (this.auth.isAuthenticated) {
      console.log('transition to manage');
      // this.transitionTo('manage');
    }
  }

  async model() {
    this.object = {};
    return this.object
  }

}
