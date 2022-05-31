import Model, { attr } from '@ember-data/model';

export default class SystemModel extends Model {
  @attr name
  @attr is_enable
  @attr help
  @attr type
}
