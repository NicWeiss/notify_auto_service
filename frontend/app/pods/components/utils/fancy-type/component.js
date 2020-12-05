import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';


export default class FancyType extends Component {
  @tracked type = null;

  init() {
    super.init(...arguments);
  }
}