import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object'

export default class NotifyShowRoute extends Route {
  @service store;
  @tracked object = null;

  model(params) {
    return this.store.findRecord('notify', params.notify_id);
  }

  @action
  onClose(){
    this.transitionTo('manage.notifications');
  }

  @action
  onEdit(notify_id){
    this.transitionTo('manage.notifications.edit', notify_id);
  }
}
