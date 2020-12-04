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

  // @action
  // edit(notify){
  //   this.transitionTo('manage/edit', notify.id);
  // }

  // @action
  // show(notify){
  //   this.transitionTo('manage/show', notify.id);
  // }
}
