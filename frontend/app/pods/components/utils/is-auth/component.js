import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';



export default class iIsAuthComponent extends Component {
  @service session;

  @tracked isAuth = false;

  constructor(owner, args) {
    super(owner, args);
    console.log('auth check');
    if (this.session.isAuthenticated) {
      this.isAuth = true;
    }
  }


}