import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class AcceptorsListRoute extends Route {
  @service store;
  @tracked object = null;

  async model() {
    return await this.store.findAll('acceptor');
  }
}
