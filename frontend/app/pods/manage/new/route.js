import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class NewRoute extends Route {
  @tracked object = null;

  async model() {
    //return this.store.findAll('list');
    this.object = [];
    return this.object
  }
}
