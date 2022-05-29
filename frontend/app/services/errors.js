import Service from '@ember/service';

export default Service.extend({
  session: Ember.inject.service(),

  checkStatus(status) {
    if (status == 403 || status == 401) {
      console.log('error');
      this.session.invalidate();
      Ember.getOwner(this).lookup('router:main').transitionTo('auth.login')
    }
  },

  handle(error) {
    console.error(error);

    if (error) {
      if (error.status) {
        this.checkStatus(error.status);
      }

      if (error.errors) {
        if (error.errors[0]) {
          if (error.errors[0].status) {
            this.checkStatus(error.errors[0].status);
          }
        }
      }
    }
  },

})
