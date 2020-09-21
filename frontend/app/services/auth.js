// ember-js-auth/app/services/auth.js
import Service from '@ember/service';
import { computed } from '@ember/object';

export default Service.extend({

  isAuthenticated: computed(function () {
    console.log('token', this.getCookie('token'));
    return this.getCookie('token') !== undefined
  }),

  authState: function(){
    console.log('token', this.getCookie('token'));
    return this.getCookie('token') !== undefined
  },

  userLogin: computed(function () {
    return this.getCookie('login')
  }),

  setSesion(login, token) {
    document.cookie = "login=" + login + "; max-age=3600";
    document.cookie = "token=" + token + "; max-age=3600";
  },

  getCookie(key) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${key}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
  },

  logout() {
    document.cookie = "login=; max-age=0";
    document.cookie = "token=; max-age=0";
  }
});