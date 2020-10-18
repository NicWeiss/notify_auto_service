import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { action } from '@ember/object';

export default class AcceptorsNewRoute extends Route {
  @service store;
  @service notify;

  async model() {
    return this.store.createRecord('acceptor-new');
  }

  @action
  onComplete() {
    this.transitionTo('manage.acceptors.list');
    this.notify.info('Получатель добавлен');
  }
}
