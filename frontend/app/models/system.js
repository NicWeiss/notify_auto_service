import Model, { attr } from '@ember-data/model';

export default class SystemModel extends Model {
  @attr name
  @attr isEnable
  @attr isSystem
  @attr help
  @attr type
}
