import BaseAuthenticator from 'ember-simple-auth/authenticators/base';
import { inject as service } from '@ember/service';
import { isEmpty } from '@ember/utils';

export default BaseAuthenticator.extend({

  restore(data) {
    return Ember.RSVP.resolve(data);
  },

  authenticate(sessionData) {
    return Ember.RSVP.resolve(sessionData);
  },

  invalidate() {
    return Ember.RSVP.resolve();
  }

});
