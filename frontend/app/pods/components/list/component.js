import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class ListComponent extends Component {
  @service notify;

  @tracked selectedNotify = null;
  

  @action
  onSelectNotify(notify) {
    this.selectedNotify = notify;
    console.log(notify);
    console.log('set notify');
  }

}
