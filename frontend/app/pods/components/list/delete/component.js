import Component from '@glimmer/component';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

import { PERIODIC } from 'frontend/constants';

export default class NotifyDeleteComponent extends Component {
  @service notify;

  PERIODIC = PERIODIC

  constructor(owner, args)  {
    super(owner, args)
  }

  @action
  onDelete() {
    this.args.notify.destroyRecord();
    this.args.onClose();
  }
}
