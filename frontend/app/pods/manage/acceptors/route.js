import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object'

export default class AcceptorsListRoute extends Route {
  @service store;
  @tracked object = null;

  async model() {
    return await this.store.findAll('acceptor');
  }

  @action
  onEdit(acceptor) {
    this.transitionTo('manage.acceptors.edit', acceptor.id);
  }
}
