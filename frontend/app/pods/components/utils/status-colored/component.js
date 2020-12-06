import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';


export default class FancyType extends Component {
  @tracked status = null;
  tagName = '';

  init() {
    super.init(...arguments);
  }
}