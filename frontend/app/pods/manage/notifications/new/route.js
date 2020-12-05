import Route from '@ember/routing/route';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

export default class NewRoute extends Route {
  @service notify;
  @tracked object = null;

  async model() {
    this.object = [];
    return this.object
  }

  @action
  onComplete() {
    this.transitionTo('manage.notifications');
    this.notify.info('Уведомление добавлено');
  }

  @action
  onClose(){
    this.transitionTo('manage.notifications');
  }
}
