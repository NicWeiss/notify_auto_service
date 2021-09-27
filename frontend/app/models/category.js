import Model, { attr } from '@ember-data/model';

export default class CategoryModel extends Model {
  @attr('string') name
  @attr('number') userId
}
