import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object'
import { getOwner } from '@ember/application';

export default class NotifyRoute extends Route {
  @service store;
  @service infinity;
  @tracked object = null;


  model() {
    const model = this.infinity.model('notify');
    this.waitReachInfinity(model);
    return {
      data: model,
      isInfinityReached: false
    };
  }

  waitReachInfinity(model) {
    setInterval(async function () {
      let infinityModel = await model;

      if (infinityModel.reachedInfinity) {
        // model.isInfinityReached = true;
      }
    }, 1000);
  }

  @action
  show(notify) {
    this.transitionTo('manage.notifications.show', notify.id);
  }

  @action
  onEdit(notify) {
    this.transitionTo('manage.notifications.edit', notify.id);
  }

  @action
  onRefresh() {
    this.refreshModel()
  }

  refreshModel() {
    this.refresh();
  }
}
