import RESTSerializer from '@ember-data/serializer/rest';
import { EmbeddedRecordsMixin } from '@ember-data/serializer/rest';

export default class NotifySerializer extends RESTSerializer.extend(EmbeddedRecordsMixin){
  // keyForAttribute(key) {
  //   console.log(Ember.String.camelize(key));
  //   return Ember.String.camelize(key);
  // };

  attrs = {
    acceptorsList: { embedded: 'always' },
    timeZoneOffset: 'time_zone_offset',
    dayOfWeek: 'day_of_week'
  };
};