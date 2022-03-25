import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';



export default class AcceptorsListComponent extends Component {
  @service store;

  @tracked selectedModel = null;
  @tracked isShowDeleteModal = false;
  @tracked isShowEditModal = false;

  constructor(owner, args) {
    super(owner, args);
  }

  closeModalWondows() {
    this.isShowDeleteModal = false;
    this.isShowEditModal = false;
  }


  @action
  onCancel() {
    this.closeModalWondows();
  }

  @action
  onAdd() {
    this.selectedModel = this.store.createRecord('acceptor');
    this.isShowEditModal = true;
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
  onComplete() {
    this.isShowAddModal = false;
    this.closeModalWondows();
    this.args.reloadModel();
  }

}
