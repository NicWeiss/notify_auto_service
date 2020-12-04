import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object'

export default class IndexRoute extends Route {
  @service store;
  @tracked object = null;

  async model() {
    return await this.store.findAll('notify');
  }

  @action
  show(notify){
    this.transitionTo('manage.list.show', notify.id);
  }

  @action
  onRefresh(){
    this.refreshModel()
  }

  refreshModel() {
    this.refresh();
  }
}
