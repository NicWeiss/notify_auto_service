import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class IndexRoute extends Route {
  @service store;
  @service auth;
  @tracked object = null;

  beforeModel(){
    if (!this.auth.isAuthenticated) {
      console.log('not Authenticated back to auth');
      this.transitionTo('auth');
    }
  }


  async model() {
    //return this.store.findAll('list');
    this.object = [
      {
        'id': 1,
        'name': 'check in',
        'date': 'every work day',
        'time': '15:00'
      },
      {
        'id': 1,
        'name': 'water',
        'date': 'every last month day',
        'time': '20:00'
      }
    ];
    return this.object
  }
}
