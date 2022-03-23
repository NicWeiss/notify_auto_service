import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class AcceptorsListComponent extends Component {
  @service notify;

  @tracked selectedModel = null;
  @tracked isShowAddModal = false;
  @tracked isShowDeleteModal = false;
  @tracked isShowEditModal = false;

  constructor(owner, args) {
    super(owner, args);
  }

  closeModalWondows() {
    this.isShowAddModal = false;
    this.isShowDeleteModal = false;
    this.isShowEditModal = false;
  }

  @action
  onAdd() {
    this.isShowAddModal = true;
  }

  @action
  onEdit(model) {
    this.selectedModel = model;
    this.isShowEditModal = true;
  }

  @action
  onDelete(model) {
    this.selectedModel = model;
    this.isShowDeleteModal = true;
  }

  @action
  onCancel() {
    this.closeModalWondows();
  }

  @action
  onComplete() {
    this.isShowAddModal = false;
    this.closeModalWondows();
    this.args.reloadModel();
  }
}
