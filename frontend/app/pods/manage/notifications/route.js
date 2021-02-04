import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object'

export default class NotifyRoute extends Route {
  @service store;
  @service infinity;
  @tracked object = null;


  model() {
    let controller = this.controllerFor(this.get('routeName'));
    const model = this.infinity.model('notify');
    this.waitReachInfinity(model, controller);

    return {
      data: model
    };
  }

  waitReachInfinity(model, controller) {
    setInterval(async function () {
      let infinityModel = await model;
      if (infinityModel.reachedInfinity) {
        controller.set('isInfinityReached', true);
      }
    }, 500);
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
