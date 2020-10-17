import RESTAdapter from '@ember-data/adapter/rest';

export default RESTAdapter.extend({
  // don't pluralize, just return type as-is
  pathForType: function (type) {
    return type;
  },
  get headers() {
    let session = "";
    let obj = JSON.parse(localStorage.getItem('ember_simple_auth-session')).authenticated;

    for (const [key, value] of Object.entries(obj)) {
      if (!parseInt(key)) {
        continue;
      }
      session += value;
    }
    return {
      'session': session
    };
  },
  namespace: 'api'
});

