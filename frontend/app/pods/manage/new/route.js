import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class NewRoute extends Route {
  @service store;
  @service auth;
  @tracked object = null;

  beforeModel(){
    if (!this.auth.isAuthenticated) {
      console.log('transition');
      // this.transitionTo('auth');
    }
  }

  async model() {
    //return this.store.findAll('list');
    console.log('new');
    this.object = [];
    return this.object
  }
}
