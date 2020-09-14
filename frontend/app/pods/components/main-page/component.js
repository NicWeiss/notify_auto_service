import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';



export default class MainPageComponent extends Component {
  @tracked selectedNotify = null;


  @action
  onSelectNotify(notify){
    this.selectedNotify=notify;
    console.log(notify);
    console.log('set notify');
  }

}
