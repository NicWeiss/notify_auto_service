import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object'

export default class SettingsListRoute extends Route {
  @service store;
  @service infinity;
  @tracked object = null;

  model() {
    // return this.store.findAll('category');
  }

  @action
  transitionToCategories() {
    this.transitionTo('manage.categories');
  }

  @action
  transitionToAceptorsSettings() {
    // this.transitionTo('manage.settings');
  }

  @action
  transitionToCategorySettings() {
    // this.transitionTo('manage.settings');
  }
}
