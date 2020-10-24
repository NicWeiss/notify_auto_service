import Model, { attr } from '@ember-data/model';

export default class NotifyNewModel extends Model {
  @attr('string') name
  @attr('string') text
  @attr('string') periodic
  @attr('string') dayOfWeek
  @attr('string') date
  @attr('string') time
  @attr('string') acceptors
  @attr('string') timeZoneOffset
}
