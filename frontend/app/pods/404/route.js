import Route from '@ember/routing/route';

export default class NotFoundRoute extends Route {
  async model() {
    setTimeout(() => {
      this.transitionTo('auth.login');
    }, 3000);
    return {}
  }
}
