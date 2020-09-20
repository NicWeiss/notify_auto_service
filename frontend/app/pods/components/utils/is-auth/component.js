import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';



export default class iIsAuthComponent extends Component {
  @tracked isAuth = false;

  constructor(owner, args) {
    super(owner, args);
    //document.cookie = "user=John; max-age=0";
    // console.log(this.getCookie('user'));
    // if (this.getCookie('user')) {
    //   this.isAuth = true;
    // }
  }


}
