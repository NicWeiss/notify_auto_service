import Component from '@glimmer/component';
import { action } from '@ember/object';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';

import { PERIODIC } from 'frontend/constants';

export default class NotifyDeleteComponent extends Component {
  @service notify;

  PERIODIC = PERIODIC

  @tracked password = '';

  constructor(owner, args) {
    super(owner, args)
  }

  @action
  onCancel() {
    this.args.onCancel()
  }

  @action
  async onDelete() {
    this.args.onDelete(this.password)
  }
}
