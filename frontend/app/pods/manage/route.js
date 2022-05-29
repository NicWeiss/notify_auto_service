import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class ManageRoute extends Route {
  @service errors;
  @service session;

  @tracked object = null;

  beforeModel() {
    let self = this;

    Ember.onerror = function (error) {
      self.errors.handle(error)
    };

    if (!self.session.isAuthenticated) {
      self.transitionTo('auth.login');
    }
  }
}
