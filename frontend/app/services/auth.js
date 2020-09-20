// ember-js-auth/app/services/auth.js
import Service from '@ember/service';
import { computed } from '@ember/object';

export default Service.extend({

  isAuthenticated: computed(function () {
    console.log('token',  this.getCookie('token'));
    return this.getCookie('token') !== undefined
  }),

  userLogin: computed(function () {
    return this.getCookie('login')
  }),

  setSesion(login, token) {
    console.log(login, token);
    document.cookie = "token=" + token + "; max-age=3600";
  },

  getCookie(key) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${key}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
  },

  logout() {
    document.cookie = ""
  }
});