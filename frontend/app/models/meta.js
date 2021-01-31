import Model, { attr } from '@ember-data/model';

export default class MetaModel extends Model {
  @attr('string') total_pages
  @attr('string') count
}
