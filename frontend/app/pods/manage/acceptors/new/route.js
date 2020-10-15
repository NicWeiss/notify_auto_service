import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class AcceptorsNewRoute extends Route {
  @service store;

  async model() {
    return this.store.createRecord('acceptor-new');
  }
}
