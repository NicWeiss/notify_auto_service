import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';

export default class AuthRoute extends Route {
  @service store;
  @service auth;
  @tracked object = null;

  beforeModel() {
    if (this.auth.isAuthenticated) {
      console.log('transition');
      this.transitionTo('manage');
    }
  }

  async model() {
    console.log('auth');
    this.object = [];
    return this.object
  }

  onLoginDone() {
    return 'transition here';
    // this.transitionTo('manage');
  }
}
