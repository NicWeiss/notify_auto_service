import Component from '@glimmer/component';
import { inject as service } from '@ember/service';

export default class NotifyComponent extends Component {
  @service notify;

  constructor(owner, args) {
    super(owner, args);
    console.log('NotifyComponent');

    //this.notify.error(this.args.post.message);
  }
}
