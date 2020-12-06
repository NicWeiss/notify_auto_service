import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { action } from '@ember/object';

export default class AcceptorsNewRoute extends Route {
  @service store;
  @service notify;

  async model() {
    return this.store.createRecord('acceptor', { reload: true });
  }

  @action
  onComplete() {
    this.transitionTo('manage.acceptors');
    this.notify.info('Получатель добавлен');
    this.onRefresh();
  }

  @action
  close() {
    this.transitionTo('manage.acceptors');
  }

  @action
  onRefresh() {
    this.refreshModel()
  }

  refreshModel() {
    this.refresh();
  }
}
