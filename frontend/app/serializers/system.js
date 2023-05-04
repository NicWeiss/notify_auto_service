import RESTSerializer from '@ember-data/serializer/rest';
import { EmbeddedRecordsMixin } from '@ember-data/serializer/rest';

export default class SystemSerializer extends RESTSerializer.extend(EmbeddedRecordsMixin) {
  attrs = {
    isSystem: 'is_system'
  };
};
