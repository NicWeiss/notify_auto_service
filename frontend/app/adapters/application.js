import RESTAdapter from '@ember-data/adapter/rest';

export default RESTAdapter.extend({
  get headers() {
    let session = "";
    let obj = JSON.parse(localStorage.getItem('ember_simple_auth-session')).authenticated;

    for (const [key, value] of Object.entries(obj)) {
      if (isNaN(key)) {
        continue;
      }
      session += value;
    }

    return {
      'session': session
    };
  },
  namespace: 'api/v2'
});

