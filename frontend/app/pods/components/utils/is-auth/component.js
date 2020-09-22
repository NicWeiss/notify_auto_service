import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';



export default Component.extend({
  session: service(),

  isAuth: tracked(),

  didRender: function(){
    if (this.session.isAuthenticated && !this.isAuth) {
      this.isAuth = true;
    } 
    if (!this.session.isAuthenticated && this.isAuth) {
      this.isAuth = false;
    }
  }
})