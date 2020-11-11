import RESTSerializer from '@ember-data/serializer/rest';
import { EmbeddedRecordsMixin } from '@ember-data/serializer/rest';

export default class AcceptorSerializer extends RESTSerializer.extend(EmbeddedRecordsMixin){
  attrs = {
    systemId: 'system_id',
  };
};
