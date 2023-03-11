import axios from 'axios'
import store from '../store'

export default async(keyword) => {
  let defaultLanguage = '';
  if (store.state.defaultLanguage !== null) {
    defaultLanguage = (store.state.defaultLanguage).toLowerCase();
  }

  let url = process.env.VUE_APP_API_ENDPOINT + 'app/user'

  if (keyword) {
    url = url + '?search=' + encodeURIComponent(keyword);
  }

  return await axios({
    url: url,
    method: 'get',
    headers: {
      'X-localization': defaultLanguage,
    }
  })
  .then(({ data: { data }}) => data)
  .catch(function() {});


}