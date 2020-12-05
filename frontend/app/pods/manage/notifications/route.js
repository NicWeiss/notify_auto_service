import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object'

export default class NotifyRoute extends Route {
  @service store;
  @tracked object = null;

  async model() {
    return await this.store.findAll('notify', { reload: true });
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
