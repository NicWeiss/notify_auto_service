import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';

export default class ManageRoute extends Route {
  @service store;
  @service session;
  @tracked object = null;

  beforeModel() {
    if (!this.session.isAuthenticated) {
      this.transitionTo('auth.login');
    }
  }

  // async model() {
  //   console.log('Auth login model: start');
  //   this.object = {};
  //   return this.object
  // }

  // @action
  // onLoginDone() {
  //   console.log('login done. to manage');
  //   window.location.reload(true);
  // }

}
