import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';


export default class FancyType extends Component {
  @tracked status=null;

  constructor(owner, args) {
    super(owner, args);
    this.status = this.args.status;
  }
}