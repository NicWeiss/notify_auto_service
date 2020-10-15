import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';


export default class AcceptorsNewComponent extends Component {
  // @tracked help = null;

  constructor(owner, args) {
    super(owner, args);
  }

  @action
  onChangeSystem(value) {
    // console.log(value);
  }

  @action
  onComplete() {
    console.log('send to backend');
    this.args.model.save()
  }
}
