import Route from '@ember/routing/route';

export default class AuthUpRoute extends Route {

  beforeModel(transition) {
    if (transition.to.name === 'auth') {
      this.transitionTo('auth.login');
    }
  }

}
