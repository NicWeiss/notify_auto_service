import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class NewRoute extends Route {
  @service store;
  @service auth;
  @tracked object = null;

  beforeModel(){
    const isAuth = this.auth.isAuthenticated;
    if (!isAuth) {
      console.log('not Authenticated back to auth');
      this.transitionTo('auth');
    }
  }

  async model() {
    //return this.store.findAll('list');
    this.object = [];
    return this.object
  }
}
