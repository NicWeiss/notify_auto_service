import Route from '@ember/routing/route';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

export default class NewRoute extends Route {
  @service notify;
  @tracked object = null;

  model(params) {
    return this.store.findRecord('notify', params.notify_id,  {reload: true});
  }

  @action
  onComplete() {
    this.transitionTo('manage.notifications');
    this.notify.info('Уведомление сохранено');
  }

  @action
  onClose(){
    this.transitionTo('manage.notifications');
  }
}
