import Route from '@ember/routing/route';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

export default class AcceptorEditRoute extends Route {
  @service notify;
  @tracked object = null;

  model(params) {
    return this.store.findRecord('acceptor', params.acceptor_id, { reload: true });
  }

  @action
  onComplete() {
    this.transitionTo('manage.acceptors');
    this.notify.info('Получатель сохранён');
  }

  @action
  close() {
    this.transitionTo('manage.acceptors');
  }
}
