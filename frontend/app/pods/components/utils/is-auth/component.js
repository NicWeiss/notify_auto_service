import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';



export default class iIsAuthComponent extends Component {
  @service auth;

  @tracked isAuth = false;

  constructor(owner, args) {
    super(owner, args);
    if (this.auth.isAuthenticated) {
      this.isAuth = true;
    }
  }


}
