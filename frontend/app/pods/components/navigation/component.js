import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';


export default class NavigationComponent extends Component {

  constructor(owner, args) {
    super(owner, args);
    this.date = new Date();
  }

  @action
  onDropdown(){ 
    if ($('#nav-dropdown').css("display") === "none") {
      $('#nav-dropdown').css("display", "block");
    } else {
      $('#nav-dropdown').css("display", "none");
    }
  }
}
