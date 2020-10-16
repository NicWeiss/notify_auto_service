import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class AcceptorsNewComponent extends Component {
  @service store;

  @tracked selectedSystem = null;

  constructor(owner, args) {
    super(owner, args);
  }

  @action
  onSelectSystem(value) {
    console.log(value);
  }

  @action
  onComplete() {
    console.log('send to backend');
    this.args.model.save()
  }
}
