import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';

export default class ManageRoute extends Route {
  @service store;
  @service session;
  @tracked object = null;

  beforeModel() {
    let self = this;

    Ember.onerror = function (error) {
      console.error(error);

      if (error?.errors) {
        if (error.errors[0].status == 403) {
          console.log('error');
          self.session.invalidate();
          self.transitionTo('auth.login');
        }
      }
    };

    if (!this.session.isAuthenticated) {
      this.transitionTo('auth.login');
    }
  }

}
