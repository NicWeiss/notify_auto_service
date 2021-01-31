import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object'

export default class AcceptorsListRoute extends Route {
  @service store;
  @service infinity;
  @tracked object = null;

  model() {
    let controller = this.controllerFor(this.get('routeName'));
    const model = this.infinity.model('acceptor');
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
  onEdit(acceptor) {
    this.transitionTo('manage.acceptors.edit', acceptor.id);
  }
}
