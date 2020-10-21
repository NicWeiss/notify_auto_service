import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';


export default class FancyType extends Component {
  @tracked type=null;

  constructor(owner, args) {
    super(owner, args);
    this.type = this.args.type;
  }
}