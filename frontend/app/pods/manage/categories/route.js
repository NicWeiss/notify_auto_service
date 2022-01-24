import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object'

export default class CategoryListRoute extends Route {
  @service store;
  @service infinity;
  @tracked object = null;

  model() {
    return this.store.findAll('category');
  }

  @action
  onAdd() {
    console.log('try add new category');
    // this.transitionTo('manage.cateories.new', acceptor.id);
  }


  @action
  transitionToSettings() {
    this.transitionTo('manage.settings');
  }
}
